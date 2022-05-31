<?php

namespace Drupal\aetg_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines AetgController class.
 */
class AetgController extends ControllerBase {

  /**
   * Redirect to current user profile page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the redirect response.
   */
  public function profile() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $url = Url::fromUserInput('/user/' . \Drupal::currentUser()->id() . '/profile');
      return new RedirectResponse($url->toString());
    }

    throw new NotFoundHttpException();
  }

  /**
   * Redirect to current user orders page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the redirect response.
   */
  public function orders() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $url = Url::fromUserInput('/user/' . \Drupal::currentUser()->id() . '/orders');
      return new RedirectResponse($url->toString());
    }

    throw new NotFoundHttpException();
  }

  /**
   * Redirect to current user invoices page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the redirect response.
   */
  public function invoices() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $url = Url::fromUserInput('/user/' . \Drupal::currentUser()->id() . '/invoices');
      return new RedirectResponse($url->toString());
    }

    throw new NotFoundHttpException();
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function dashboard() {
    return [];
  }

}
