<?php

namespace Drupal\vault_extra\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Renders an add/remove favorite link for a vault_file row.
 *
 * @ViewsField("vault_extra_favorite_toggle")
 */
class FavoriteToggleField extends FieldPluginBase {

  public function query() {
    // No query changes needed; we use the row's node entity directly.
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  public function render(ResultRow $values) {
    $node = $this->getEntity($values);
    if (!$node) {
      return '';
    }

    $uid = (int) \Drupal::currentUser()->id();
    $is_favorite = (bool) \Drupal::database()->select('vault_extra_favorite', 'f')
      ->fields('f', ['id'])
      ->condition('uid', $uid)
      ->condition('nid', $node->id())
      ->execute()
      ->fetchField();

    $label = $is_favorite ? $this->t('Unfavorite') : $this->t('Favorite');
    $url = Url::fromRoute('vault_extra.favorite_toggle', ['node' => $node->id()], [
      'query' => ['destination' => Url::fromRoute('<current>')->toString()],
    ]);

    return Link::fromTextAndUrl($label, $url)->toRenderable();
  }

}
