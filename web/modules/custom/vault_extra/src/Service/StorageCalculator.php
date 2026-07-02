<?php

namespace Drupal\vault_extra\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Calculates private-file storage usage per user for the vault.
 */
class StorageCalculator {

  /**
   * Default per-user storage quota in bytes (100 MB), local-dev default.
   */
  const QUOTA_BYTES = 104857600;

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
   * Returns the per-user storage quota in bytes.
   */
  public function getQuotaBytes(): int {
    return self::QUOTA_BYTES;
  }

}
