<?php

namespace Drupal\aetg_invoice\Plugin\Field\FieldFormatter;

use Drupal\aetg_invoice\InvoiceService;
use Drupal\commerce_order\Adjustment;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'aetg_commerce_invoice_total_tax' formatter.
 *
 * @FieldFormatter(
 *   id = "aetg_commerce_invoice_total_tax",
 *   label = @Translation("Invoice total tax"),
 *   field_types = {
 *     "commerce_price",
 *   },
 * )
 */
class AetgInvoiceTotalTax extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The invoice total summary service.
   *
   * @var \Drupal\aetg_invoice\InvoiceService
   */
  protected $invoiceService;

  /**
   * Constructs a new InvoiceTotalSummary object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\aetg_invoice\InvoiceService $invoice_service
   *   The invoice total summary service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, InvoiceService $invoice_service) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->invoiceService = $invoice_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('aetg_invoice.invoice_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice */
    $invoice = $items->getEntity();
    $elements = [];
    if (!$items->isEmpty()) {
      $total_taxes = $this->invoiceService->getTaxes($invoice);

      // Convert the adjustments to arrays.
      $total_taxes = array_map(function (Adjustment $adjustment) {
        return $adjustment->toArray();
      }, $total_taxes);

      $elements[0] = [
        '#theme' => 'aetg_commerce_invoice_total_tax',
        '#invoice_entity' => $invoice,
        '#total_tax' => $total_taxes,
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_invoice' && $field_name == 'total_price';
  }

}
