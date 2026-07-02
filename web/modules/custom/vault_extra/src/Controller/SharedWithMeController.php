<?php

namespace Drupal\vault_extra\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lists vault files that other users have shared with the current user.
 */
class SharedWithMeController extends ControllerBase {

  public function __construct(protected \Drupal\Core\Database\Connection $database) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function list() {
    $uid = (int) $this->currentUser()->id();

    $rows = $this->database->select('vault_extra_share', 's')
      ->fields('s', ['nid', 'owner_uid'])
      ->condition('shared_with_uid', $uid)
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();

    $items = [];
    if ($rows) {
      $node_storage = $this->entityTypeManager()->getStorage('node');
      $user_storage = $this->entityTypeManager()->getStorage('user');
      foreach ($rows as $row) {
        $node = $node_storage->load($row->nid);
        if (!$node || !$node->access('view')) {
          continue;
        }
        $owner = $user_storage->load($row->owner_uid);
        $items[] = [
          'title' => $node->label(),
          'url' => Link::fromTextAndUrl($node->label(), Url::fromRoute('entity.node.canonical', ['node' => $node->id()]))->toString(),
          'category' => $node->get('field_category')->value,
          'owner' => $owner ? $owner->getDisplayName() : $this->t('Unknown user'),
          'action' => '',
        ];
      }
    }

    return [
      '#theme' => 'vault_extra_file_list',
      '#empty_message' => $this->t('No one has shared any files with you yet.'),
      '#items' => $items,
      '#attached' => ['library' => ['vault_extra/vault_extra']],
    ];
  }

}
