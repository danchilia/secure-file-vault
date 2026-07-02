<?php

namespace Drupal\vault_extra\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Renders the file owner's storage usage next to each row in the admin
 * "All Vault Files" view.
 *
 * @ViewsField("vault_extra_storage_usage")
 */
class StorageUsageField extends FieldPluginBase {

  public function query() {
    // No query changes needed; we use the row's node entity directly.
  }

  public function render(ResultRow $values) {
    $node = $this->getEntity($values);
    if (!$node) {
      return '';
    }

    /** @var \Drupal\vault_extra\Service\StorageCalculator $calculator */
    $calculator = \Drupal::service('vault_extra.storage_calculator');
    $uid = (int) $node->getOwnerId();
    $used = $calculator->getUsedBytes($uid);
    $quota = $calculator->getQuotaBytes($uid);
    $plan = $calculator->getPlan($uid);

    return $this->t('@used / @quota (@plan)', [
      '@used' => format_size($used),
      '@quota' => format_size($quota),
      '@plan' => $plan ? $plan->label() : $this->t('No plan'),
    ]);
  }

}
