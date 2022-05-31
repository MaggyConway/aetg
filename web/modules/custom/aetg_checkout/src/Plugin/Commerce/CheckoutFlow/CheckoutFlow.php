<?php

namespace Drupal\aetg_checkout\Plugin\Commerce\CheckoutFlow;

use Drupal\aetg_common\CommonFunctions;
use Drupal\commerce_checkout\CheckoutPaneManager;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Extends the default multistep checkout flow.
 *
 * @CommerceCheckoutFlow(
 *   id = "aetg_checkoutflow",
 *   label = "AETG CheckoutFlow",
 * )
 */
class CheckoutFlow extends CheckoutFlowWithPanesBase {

  /**
   * The Common Functions Service.
   *
   * @var \Drupal\aetg_common\CommonFunctions
   */
  protected $customFunctions;

  /**
   * Constructs a new CheckoutFlowWithPanesBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pane_id
   *   The plugin_id for the plugin instance.
   * @param mixed $pane_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\commerce_checkout\CheckoutPaneManager $pane_manager
   *   The checkout pane manager.
   * @param \Drupal\aetg_common\CommonFunctions $custom_functions
   *   The custom functions service.
   */
  public function __construct(array $configuration, $pane_id, $pane_definition, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher, RouteMatchInterface $route_match, CheckoutPaneManager $pane_manager, CommonFunctions $custom_functions) {
    parent::__construct($configuration, $pane_id, $pane_definition, $entity_type_manager, $event_dispatcher, $route_match, $pane_manager);

    $this->customFunctions = $custom_functions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pane_id, $pane_definition) {
    return new static(
      $configuration,
      $pane_id,
      $pane_definition,
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('current_route_match'),
      $container->get('plugin.manager.commerce_checkout_pane'),
      $container->get('aetg_common.functions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Note that previous_label and next_label are not the labels
    // shown on the step itself. Instead, they are the labels shown
    // when going back to the step, or proceeding to the step.
    return [
        'login' => [
          'label' => $this->t('Login'),
          'previous_label' => $this->t('Go back'),
          'has_sidebar' => FALSE,
        ],
        'order_information' => [
          'label' => $this->t('Order information'),
          'has_sidebar' => TRUE,
          'previous_label' => $this->t('Go back'),
        ],
        'review' => [
          'label' => $this->t('Review'),
          'next_label' => $this->t('Continue to review'),
          'previous_label' => $this->t('Go back'),
          'has_sidebar' => TRUE,
        ],
      ] + parent::getSteps();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($this->order->bundle() == 'event') {
      $is_event_invalid = FALSE;

        $getItems = $this->order->getItems();

        foreach ($getItems as $orderItem) {
          $purchased_entity = $orderItem->getPurchasedEntity();

          if ($purchased_entity instanceof ProductVariation) {

            $field_name = 'field_node';
            if ($purchased_entity->hasField($field_name)) {

              if ($purchased_entity->get($field_name)->isEmpty()) {
                $is_event_invalid = TRUE;
                break;
              }

              if (($node = current($purchased_entity->get($field_name)->referencedEntities())) && $node instanceof NodeInterface) {
                // If the event has already started or has passed.
                $is_event_invalid = $this->customFunctions->lateForEvent($node, 0);
              }
            }
          }
        }

      if ($is_event_invalid) {
        $message = $this->t('El evento ya ha comenzado o ha pasado. Por favor, vaciar el carrito de compras.');
        $form_state->setError($form, $message);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}
