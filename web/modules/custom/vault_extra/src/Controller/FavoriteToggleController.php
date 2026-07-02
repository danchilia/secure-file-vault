<?php

namespace Drupal\vault_extra\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Toggles a favorite flag for the current user on a vault file.
 */
class FavoriteToggleController extends ControllerBase {

  use RedirectDestinationTrait;

  public function __construct(protected \Drupal\Core\Database\Connection $database) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function toggle(NodeInterface $node): RedirectResponse {
    $uid = (int) $this->currentUser()->id();

    $exists = $this->database->select('vault_extra_favorite', 'f')
      ->fields('f', ['id'])
      ->condition('uid', $uid)
      ->condition('nid', $node->id())
      ->execute()
      ->fetchField();

    if ($exists) {
      $this->database->delete('vault_extra_favorite')
        ->condition('id', $exists)
        ->execute();
      $this->messenger()->addStatus($this->t('Removed %title from favorites.', ['%title' => $node->label()]));
    }
    else {
      $this->database->insert('vault_extra_favorite')
        ->fields([
          'uid' => $uid,
          'nid' => $node->id(),
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();
      $this->messenger()->addStatus($this->t('Added %title to favorites.', ['%title' => $node->label()]));
    }

    $destination = $this->getDestinationArray();
    $url = isset($destination['destination']) ? Url::fromUserInput($destination['destination']) : Url::fromRoute('vault_extra.favorites');

    return new RedirectResponse($url->setAbsolute()->toString());
  }

}
