<?php

namespace Drupal\vault_extra\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Access check for the share and revoke-share routes: owner or admin only.
 */
class VaultShareAccessCheck {

  public function access(NodeInterface $node, AccountInterface $account) {
    if ($node->bundle() !== 'vault_file') {
      return AccessResult::forbidden();
    }
    if ($account->hasPermission('bypass node access') || (int) $node->getOwnerId() === (int) $account->id()) {
      return AccessResult::allowed()->cachePerUser()->addCacheableDependency($node);
    }
    return AccessResult::forbidden()->cachePerUser()->addCacheableDependency($node);
  }

}
