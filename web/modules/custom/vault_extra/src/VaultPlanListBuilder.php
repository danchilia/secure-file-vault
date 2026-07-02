<?php

namespace Drupal\vault_extra;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a draggable admin listing of vault plans.
 *
 * @see \Drupal\vault_extra\Entity\VaultPlan
 */
class VaultPlanListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vault_plan_admin_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Plan');
    $header['storage'] = $this->t('Storage limit');
    $header['price'] = $this->t('Price');
    $header['description'] = $this->t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\vault_extra\VaultPlanInterface $entity */
    // DraggableListBuilderTrait::buildForm() auto-wraps the 'label' key for
    // us, but every other custom column must be a proper render array
    // (e.g. ['#markup' => ...]) — a raw string here throws an
    // InvalidArgumentException deep in Element::children().
    $row['label'] = $entity->label();
    $row['storage'] = ['#markup' => format_size($entity->getStorageLimitBytes())];
    $row['price'] = [
      '#markup' => $entity->getPriceUsd() > 0
        ? sprintf('$%.2f / mo', $entity->getPriceUsd())
        : $this->t('Free'),
    ];
    $row['description'] = ['#markup' => $entity->getDescription()];
    return $row + parent::buildRow($entity);
  }

}
