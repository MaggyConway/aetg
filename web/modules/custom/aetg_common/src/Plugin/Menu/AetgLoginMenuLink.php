<?php

namespace Drupal\aetg_common\Plugin\Menu;

use Drupal\user\Plugin\Menu\LoginLogoutMenuLink;

/**
 * The "Sign in" link.
 */
class AetgLoginMenuLink extends LoginLogoutMenuLink {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Acceso');
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'user.login';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'session',
      'user.roles:authenticated',
    ];
  }

}
