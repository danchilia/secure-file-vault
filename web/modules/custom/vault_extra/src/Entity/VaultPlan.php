<?php

namespace Drupal\vault_extra\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\vault_extra\VaultPlanInterface;

/**
 * Defines a storage plan entity (Free / Basic / Standard / Premium, etc).
 *
 * @ConfigEntityType(
 *   id = "vault_plan",
 *   label = @Translation("Vault plan"),
 *   label_collection = @Translation("Vault plans"),
 *   label_singular = @Translation("vault plan"),
 *   label_plural = @Translation("vault plans"),
 *   label_count = @PluralTranslation(
 *     singular = "@count vault plan",
 *     plural = "@count vault plans",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\vault_extra\VaultPlanListBuilder",
 *     "form" = {
 *       "add" = "Drupal\vault_extra\Form\VaultPlanForm",
 *       "edit" = "Drupal\vault_extra\Form\VaultPlanForm",
 *       "delete" = "Drupal\vault_extra\Form\VaultPlanDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer vault plans",
 *   config_prefix = "vault_plan",
 *   static_cache = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "add-form" = "/admin/config/vault/plans/add",
 *     "edit-form" = "/admin/config/vault/plans/{vault_plan}",
 *     "delete-form" = "/admin/config/vault/plans/{vault_plan}/delete",
 *     "collection" = "/admin/config/vault/plans"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "storage_limit_mb",
 *     "price_usd",
 *     "description",
 *     "weight",
 *   }
 * )
 */
class VaultPlan extends ConfigEntityBase implements VaultPlanInterface {

  /**
   * The machine name of this plan.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of this plan.
   *
   * @var string
   */
  protected $label;

  /**
   * Storage limit in megabytes.
   *
   * @var int
   */
  protected $storage_limit_mb = 100;

  /**
   * Monthly price in US dollars. Informational only; no payment is taken.
   *
   * @var float
   */
  protected $price_usd = 0.0;

  /**
   * Optional short description shown on the plans admin list.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The weight of this plan (display order, lowest first).
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * {@inheritdoc}
   */
  public function getStorageLimitBytes(): int {
    return (int) $this->storage_limit_mb * 1024 * 1024;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageLimitMb(): int {
    return (int) $this->storage_limit_mb;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriceUsd(): float {
    return (float) $this->price_usd;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return (string) $this->description;
  }

}
