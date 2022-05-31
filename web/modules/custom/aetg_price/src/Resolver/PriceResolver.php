<?php

namespace Drupal\aetg_price\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Returns the price fetched from the product variation fields.
 */
class PriceResolver implements PriceResolverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a PriceResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {
    if (!$entity instanceof ProductVariationInterface) {
      return NULL;
    }

    $role_field = ['asociado' => 'field_member_price', 'escuela' => 'field_school_price'];

    $user_roles = array_intersect($this->currentUser->getRoles(), array_keys($role_field));

    if (empty($user_roles)) {
      return NULL;
    }

    $field_name = $role_field[current($user_roles)];

    if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
      $price = $entity->get($field_name)->getValue()[0]['value'];
      return new Price((string) $price, $context->getStore()->getDefaultCurrencyCode());
    }

    return NULL;
  }

}
