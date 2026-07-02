<?php

namespace Drupal\vault_extra\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lists the current user's favorited vault files.
 */
class FavoritesController extends ControllerBase {

  public function __construct(protected \Drupal\Core\Database\Connection $database) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function list() {
    $uid = (int) $this->currentUser()->id();

    $nids = $this->database->select('vault_extra_favorite', 'f')
      ->fields('f', ['nid'])
      ->condition('uid', $uid)
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchCol();

    $items = [];
    if ($nids) {
      $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {
        if (!$node->access('view')) {
          continue;
        }
        $items[] = [
          'title' => $node->label(),
          'url' => Link::fromTextAndUrl($node->label(), Url::fromRoute('entity.node.canonical', ['node' => $node->id()]))->toString(),
          'category' => $node->get('field_category')->value,
          'action' => Link::fromTextAndUrl($this->t('Remove favorite'), Url::fromRoute('vault_extra.favorite_toggle', ['node' => $node->id()], ['query' => ['destination' => Url::fromRoute('vault_extra.favorites')->toString()]]))->toString(),
        ];
      }
    }

    return [
      '#theme' => 'vault_extra_file_list',
      '#empty_message' => $this->t('You have not favorited any files yet. Favorite a file from the "My Files" page.'),
      '#items' => $items,
      '#attached' => ['library' => ['vault_extra/vault_extra']],
    ];
  }

}
