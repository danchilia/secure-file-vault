<?php

namespace Drupal\vault_extra\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Renders a "Share" link for a vault_file row.
 *
 * @ViewsField("vault_extra_share_link")
 */
class ShareLinkField extends FieldPluginBase {

  public function query() {
    // No query changes needed; we use the row's node entity directly.
  }

  public function render(ResultRow $values) {
    $node = $this->getEntity($values);
    if (!$node || !$node->access('update')) {
      return '';
    }

    $url = Url::fromRoute('vault_extra.share_form', ['node' => $node->id()]);
    return Link::fromTextAndUrl($this->t('Share'), $url)->toRenderable();
  }

}
