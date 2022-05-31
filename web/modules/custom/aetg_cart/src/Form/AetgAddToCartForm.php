<?php

namespace Drupal\aetg_cart\Form;

use Drupal\aetg_common\CommonFunctions;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the order item add to cart form.
 *
 * @package Drupal\kg_cart\Form
 */
class AetgAddToCartForm extends AddToCartForm {

  /**
   * The Common Functions Service.
   *
   * @var \Drupal\aetg_common\CommonFunctions
   */
  protected $customFunctions;

  /**
   * Constructs a new AddToCartForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_order\Resolver\OrderTypeResolverInterface $order_type_resolver
   *   The order type resolver.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\commerce_price\Resolver\ChainPriceResolverInterface $chain_price_resolver
   *   The chain base price resolver.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\aetg_common\CommonFunctions $custom_functions
   *   The custom functions service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store, ChainPriceResolverInterface $chain_price_resolver, AccountInterface $current_user, CommonFunctions $custom_functions) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time, $cart_manager, $cart_provider, $order_type_resolver, $current_store, $chain_price_resolver, $current_user);

    $this->customFunctions = $custom_functions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_price.chain_price_resolver'),
      $container->get('current_user'),
      $container->get('aetg_common.functions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $action_description = '';
    $node = \Drupal::routeMatch()->getParameter('node');

    if (isset($form['actions']['submit'])) {
      /** @var ProductInterface $product */
      $product = $form_state->getStorage()['product'];

      if (!empty($product) && ($product_variation = $product->getVariations())) {
        $product_variation = current($product_variation);


        if ($product->bundle() == 'event' ) {
          $form['actions']['submit']['#value'] = $this->t('Regístrate');
        }

        // Checks if user has access to this event by role.
        if ($product_variation instanceof ProductVariation && $product_variation->hasField('field_allowed_user_roles') && !$product_variation->get('field_allowed_user_roles')->isEmpty()) {
          $allowed_user_roles = $product_variation->get('field_allowed_user_roles')->getValue();
          $allowed_user_roles = array_column($allowed_user_roles, 'value');
          $roles = \Drupal::currentUser()->getRoles();
          if (!array_intersect($allowed_user_roles, $roles)) {
            $action_description = $this->t('No tienes acceso a este evento.');
          }
        }
      }
      // Checks if this event is outdated.
      if (empty($action_description) && $node instanceof NodeInterface && $node->bundle() === 'event') {
        // If the event has already started or has passed.
        if ($this->customFunctions->lateForEvent($node, 0)) {
          $action_description = $this->t('El evento ya ha comenzado o ha pasado.');
        }
      }
      if (empty($action_description)) {
        if ($cart = $this->hasProductInCart($form_state->getStorage()['product']->get('product_id')->getValue()[0]['value'])) {
          $action_description = $this->t('Este producto ya está en su carrito de compras. @checkout_link', [
            '@checkout_link' => Link::createFromRoute('Iniciar pago.', 'commerce_checkout.form', [
              'commerce_order' => $cart->id(),
            ])->toString(),
          ]);
        }
        elseif ($cart = $this->hasOrderTypeInCart('event')) {
          $action_description = $this->t('Elimine otros eventos del <a href="@href" class="cart-link--empty">carrito de compras</a>', [
            '@href' => Url::fromRoute('commerce_checkout.form', ['commerce_order' => $cart->id()])->toString(),
          ]);
        }
      }
    }

    if ($action_description) {
      $form['actions']['submit']['#disabled'] = TRUE;
      $form['actions']['submit']['description'] = [
        '#type' => 'item',
        '#markup' => $action_description,
      ];
    }

    return $form;
  }

  /**
   * Checks whether this order type is in a cart.
   *
   * @param string $order_type
   *   Order type.
   *
   * @return bool|\Drupal\commerce_order\Entity\OrderInterface
   *   cart if this order type already in the cart, FALSE otherwise.
   */
  protected function hasOrderTypeInCart($order_type) {
    $order_item = $this->entity;
    if ($order_item instanceof OrderItem) {
      $current_order_type = $this->orderTypeResolver->resolve($order_item);
    }
    else {
      return FALSE;
    }
    if ($current_order_type !== $order_type) {
      return FALSE;
    }
    $carts = $this->cartProvider->getCarts($this->currentUser());
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      // There is a chance the cart may have converted from a draft order, but
      // is still in session. Such as just completing check out. So we verify
      // that the cart is still a cart.
      return $cart->hasItems() && $cart->cart->value;
    });

    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        if ($cart->bundle() == $current_order_type) {
          return $cart;
        }
      }
    }

    return FALSE;
  }

  /**
   * Checks whether this product is in a cart.
   *
   * @param int|string $product_id
   *   Product ID.
   *
   * @return bool|\Drupal\commerce_order\Entity\OrderInterface
   *   cart if this product already in the cart, FALSE otherwise.
   */
  protected function hasProductInCart($product_id) {
    $carts = $this->cartProvider->getCarts();
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        $getItems = $cart->getItems();
        foreach ($getItems as $orderItem) {
          $purchased_entity = $orderItem->getPurchasedEntity();
          if ($purchased_entity && $purchased_entity->getProductId() == $product_id) {
            return $cart;
          }
        }
      }
    }

    return FALSE;
  }

}
