<?php

namespace Drupal\aetg_checkout\Controller;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CartController.
 *
 * @package Drupal\aetg_checkout\Controller
 */
class CartController implements ContainerInjectionInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Safe order and go to complete page.
   *
   * @param $order_id
   *   Order id.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the redirect response.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function emptyCart($order_id) {
    $url = Url::fromRoute('<front>');
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::load($order_id);
    if ($order instanceof OrderInterface
      && (\Drupal::currentUser()->id() == $order->getCustomerId())) {
      $order_items = $order->getItems();
      foreach ($order_items as $order_item) {
        $order_item->delete();
      }
      $order->delete();
      \Drupal::messenger()->addMessage('Su carrito de compras fue eliminado con éxito.');
    }

    return new RedirectResponse($url->toString());
  }

}
