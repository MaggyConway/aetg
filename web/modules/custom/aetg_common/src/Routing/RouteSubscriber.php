<?php

namespace Drupal\aetg_common\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Define custom access for '/user/{user}/profile'.
    if ($route = $collection->get('view.profiles.page_1')) {
      $route->setRequirement('_aetg_common_profile_page_access', 'Drupal\aetg_common\Access\ProfilePageAccessCheck::access');
    }

    // Change _title: 'Log in' to 'Acceso'.
    if ($route = $collection->get('user.login')) {
      $route->setDefault('_title', 'Acceso');
    }
  }

}
