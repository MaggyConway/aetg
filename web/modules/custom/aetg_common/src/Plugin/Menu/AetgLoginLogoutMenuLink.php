<?php

namespace Drupal\aetg_common\Plugin\Menu;

use Drupal\user\Plugin\Menu\LoginLogoutMenuLink;

/**
 * Change title of "Log out".
 */
class AetgLoginLogoutMenuLink extends LoginLogoutMenuLink {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    if ($this->currentUser->isAuthenticated()) {
      return $this->t('Cerrar sesión');
    }
    else {
      return $this->t('Acceso');
    }
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
