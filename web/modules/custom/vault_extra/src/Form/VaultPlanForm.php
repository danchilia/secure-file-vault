<?php

namespace Drupal\vault_extra\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add/edit form for vault_plan config entities.
 */
class VaultPlanForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\vault_extra\VaultPlanInterface $plan */
    $plan = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plan name'),
      '#maxlength' => 255,
      '#default_value' => $plan->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $plan->id(),
      '#machine_name' => [
        'exists' => '\Drupal\vault_extra\Entity\VaultPlan::load',
      ],
      '#disabled' => !$plan->isNew(),
    ];

    $form['storage_limit_mb'] = [
      '#type' => 'number',
      '#title' => $this->t('Storage limit (MB)'),
      '#description' => $this->t('The amount of private file storage, in megabytes, a user on this plan may use. 1024 MB = 1 GB.'),
      '#min' => 1,
      '#default_value' => $plan->getStorageLimitMb(),
      '#required' => TRUE,
    ];

    $form['price_usd'] = [
      '#type' => 'number',
      '#title' => $this->t('Price (USD / month)'),
      '#description' => $this->t('Informational only — no payment is actually processed. Use 0 for a free plan.'),
      '#min' => 0,
      '#step' => 0.01,
      '#default_value' => $plan->getPriceUsd(),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $plan->getDescription(),
      '#rows' => 2,
    ];

    $form['weight'] = [
      '#type' => 'value',
      '#value' => $plan->get('weight'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $plan = $this->entity;
    $status = $plan->save();

    $message = $status == SAVED_UPDATED
      ? $this->t('Saved the %label plan.', ['%label' => $plan->label()])
      : $this->t('Created the %label plan.', ['%label' => $plan->label()]);
    $this->messenger()->addStatus($message);

    $form_state->setRedirectUrl($plan->toUrl('collection'));
    return $status;
  }

}
