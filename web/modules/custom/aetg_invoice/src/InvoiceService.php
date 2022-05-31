<?php

namespace Drupal\aetg_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\AdjustmentTransformerInterface;
use Drupal\commerce_price\Price;

class InvoiceService {

  /**
   * The adjustment transformer.
   *
   * @var \Drupal\commerce_order\AdjustmentTransformerInterface
   */
  protected $adjustmentTransformer;

  /**
   * Constructs a new InvoiceService object.
   *
   * @param \Drupal\commerce_order\AdjustmentTransformerInterface $adjustment_transformer
   *   The adjustment transformer.
   */
  public function __construct(AdjustmentTransformerInterface $adjustment_transformer) {
    $this->adjustmentTransformer = $adjustment_transformer;
  }

  /**
   * @param InvoiceInterface $invoice
   * @return Adjustment[]
   */
  function getTaxes(InvoiceInterface $invoice) {
    $taxes = $invoice->collectAdjustments(['tax']);
    return $this->adjustmentTransformer->processAdjustments($taxes);
  }

  /**
   * @param InvoiceInterface $invoice
   * @return Price
   */
  function getTotalTaxAmount(InvoiceInterface $invoice) {
    $total_tax = new Price('0', 'EUR');
    $taxes = $invoice->collectAdjustments(['tax']);
    $taxes = $this->adjustmentTransformer->processAdjustments($taxes);

    foreach ($taxes as $tax) {
      if ($tax instanceof Adjustment) {
        $tax_amount = $tax->getAmount();
        $total_tax = $total_tax->add($tax_amount);
      }
    }

    return $total_tax;
  }

}
