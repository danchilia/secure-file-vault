<?php

namespace Drupal\vault_extra\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\vault_extra\VaultPlanInterface;

/**
 * Calculates private-file storage usage and plan quota per user.
 */
class StorageCalculator {

  /**
   * Fallback quota in bytes if a user has no plan and no plans exist at
   * all (e.g. an admin deleted every plan). Keeps the site usable rather
   * than dividing by zero or letting uploads through unbounded.
   */
  const FALLBACK_QUOTA_BYTES = 104857600;

  /**
   * Machine name of the plan assigned to users with no explicit plan.
   */
  const DEFAULT_PLAN_ID = 'free';

  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {}

  /**
   * Sums the size of every vault_file the given user owns.
   */
  public function getUsedBytes(int $uid): int {
    $storage = $this->entityTypeManager->getStorage('node');
    $nids = $storage->getQuery()
      ->condition('type', 'vault_file')
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->execute();

    if (!$nids) {
      return 0;
    }

    $used = 0;
    foreach ($storage->loadMultiple($nids) as $node) {
      if (!$node->get('field_vault_document')->isEmpty()) {
        $file = $node->get('field_vault_document')->entity;
        if ($file) {
          $used += (int) $file->getSize();
        }
      }
    }
    return $used;
  }

  /**
   * Gets the plan assigned to a user, falling back to the Free plan, and
   * finally to no plan at all if none exist.
   */
  public function getPlan(int $uid): ?VaultPlanInterface {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $plan_storage = $this->entityTypeManager->getStorage('vault_plan');

    $account = $user_storage->load($uid);
    if ($account && $account->hasField('field_vault_plan') && !$account->get('field_vault_plan')->isEmpty()) {
      $plan = $account->get('field_vault_plan')->entity;
      if ($plan) {
        return $plan;
      }
    }

    return $plan_storage->load(self::DEFAULT_PLAN_ID);
  }

  /**
   * Returns the given user's storage quota in bytes, based on their plan.
   */
  public function getQuotaBytes(int $uid): int {
    $plan = $this->getPlan($uid);
    return $plan ? $plan->getStorageLimitBytes() : self::FALLBACK_QUOTA_BYTES;
  }

}
