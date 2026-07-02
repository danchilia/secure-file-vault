<?php

namespace Drupal\vault_extra\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to share a vault file with another registered user.
 */
class ShareFileForm extends FormBase {

  public function __construct(protected Connection $database) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function getFormId() {
    return 'vault_extra_share_file_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['#node'] = $node;

    $form['intro'] = [
      '#markup' => '<p>' . $this->t('Share %title with another registered user. They will be able to view and download it.', ['%title' => $node->label()]) . '</p>',
    ];

    $form['shared_with'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Share with user'),
      '#description' => $this->t('Start typing a username.'),
      '#required' => TRUE,
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Share file'),
    ];

    $form['current_shares'] = $this->buildCurrentSharesTable($node);

    return $form;
  }

  protected function buildCurrentSharesTable(NodeInterface $node): array {
    $rows = $this->database->select('vault_extra_share', 's')
      ->fields('s', ['shared_with_uid'])
      ->condition('nid', $node->id())
      ->execute()
      ->fetchCol();

    if (!$rows) {
      return [
        '#markup' => '<p>' . $this->t('Not currently shared with anyone.') . '</p>',
      ];
    }

    $header = [$this->t('Shared with'), $this->t('Operations')];
    $table_rows = [];
    $users = $this->entityTypeManager()->getStorage('user')->loadMultiple($rows);
    foreach ($users as $user) {
      $revoke_url = Url::fromRoute('vault_extra.share_revoke', [
        'node' => $node->id(),
        'shared_uid' => $user->id(),
      ], [
        'query' => ['destination' => Url::fromRoute('vault_extra.share_form', ['node' => $node->id()])->toString()],
      ]);
      $table_rows[] = [
        $user->getDisplayName(),
        Link::fromTextAndUrl($this->t('Revoke'), $revoke_url)->toString(),
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $table_rows,
      '#caption' => $this->t('Currently shared with'),
    ];
  }

  protected function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form['#node'];
    $target_uid = (int) $form_state->getValue('shared_with');

    if (!$target_uid || !($user = $this->entityTypeManager()->getStorage('user')->load($target_uid))) {
      $form_state->setErrorByName('shared_with', $this->t('Select a valid registered user.'));
      return;
    }

    if ($target_uid === (int) $node->getOwnerId()) {
      $form_state->setErrorByName('shared_with', $this->t('You cannot share a file with yourself.'));
      return;
    }

    $exists = $this->database->select('vault_extra_share', 's')
      ->fields('s', ['id'])
      ->condition('nid', $node->id())
      ->condition('shared_with_uid', $target_uid)
      ->execute()
      ->fetchField();

    if ($exists) {
      $form_state->setErrorByName('shared_with', $this->t('This file is already shared with @user.', ['@user' => $user->getDisplayName()]));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form['#node'];
    $target_uid = (int) $form_state->getValue('shared_with');

    $this->database->insert('vault_extra_share')
      ->fields([
        'nid' => $node->id(),
        'owner_uid' => $node->getOwnerId(),
        'shared_with_uid' => $target_uid,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    $this->messenger()->addStatus($this->t('File shared successfully.'));
    $form_state->setRedirect('vault_extra.share_form', ['node' => $node->id()]);
  }

}
