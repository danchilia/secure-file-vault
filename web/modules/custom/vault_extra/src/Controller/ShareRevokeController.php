<?php

namespace Drupal\vault_extra\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Revokes a share of a vault file from another user.
 */
class ShareRevokeController extends ControllerBase {

  use RedirectDestinationTrait;

  public function __construct(protected \Drupal\Core\Database\Connection $database) {}

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function revoke(NodeInterface $node, int $shared_uid): RedirectResponse {
    $this->database->delete('vault_extra_share')
      ->condition('nid', $node->id())
      ->condition('shared_with_uid', $shared_uid)
      ->execute();

    $this->messenger()->addStatus($this->t('Share removed for %title.', ['%title' => $node->label()]));

    $destination = $this->getDestinationArray();
    $url = isset($destination['destination']) ? Url::fromUserInput($destination['destination']) : Url::fromRoute('vault_extra.share_form', ['node' => $node->id()]);

    return new RedirectResponse($url->setAbsolute()->toString());
  }

}
