<?php

namespace Drupal\vault_extra\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Public marketing landing page: explains the vault and where to upload.
 */
class LandingController extends ControllerBase {

  public function view() {
    $is_authenticated = $this->currentUser()->isAuthenticated();

    $primary_cta = $is_authenticated
      ? ['label' => $this->t('Go to My Files'), 'url' => Url::fromUserInput('/vault/my-files')]
      : ['label' => $this->t('Create a free account'), 'url' => Url::fromRoute('user.register')];

    $secondary_cta = $is_authenticated
      ? ['label' => $this->t('Upload a file'), 'url' => Url::fromRoute('node.add', ['node_type' => 'vault_file'])]
      : ['label' => $this->t('Log in'), 'url' => Url::fromRoute('user.login')];

    return [
      '#theme' => 'vault_extra_landing',
      '#is_authenticated' => $is_authenticated,
      '#primary_cta' => $primary_cta,
      '#secondary_cta' => $secondary_cta,
      '#attached' => ['library' => ['vault_extra/vault_extra']],
      '#cache' => ['contexts' => ['user.roles:authenticated']],
    ];
  }

}
