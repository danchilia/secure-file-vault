<?php

namespace Drupal\vault_extra;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for vault_plan config entities.
 */
interface VaultPlanInterface extends ConfigEntityInterface {

  /**
   * Gets the storage limit in bytes.
   */
  public function getStorageLimitBytes(): int;

  /**
   * Gets the storage limit in megabytes.
   */
  public function getStorageLimitMb(): int;

  /**
   * Gets the monthly price in US dollars (informational only).
   */
  public function getPriceUsd(): float;

  /**
   * Gets the plan's short description.
   */
  public function getDescription(): string;

}
