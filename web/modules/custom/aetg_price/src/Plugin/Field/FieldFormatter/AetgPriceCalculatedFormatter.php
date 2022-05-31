<?php

namespace Drupal\aetg_price\Plugin\Field\FieldFormatter;

use Drupal\commerce\Context;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Plugin\Field\FieldFormatter\PriceCalculatedFormatter;

/**
 * Alternative implementation of the 'commerce_price_calculated' formatter.
 *
 * @see \Drupal\commerce_order\Plugin\Field\FieldFormatter\PriceCalculatedFormatter
 */
class AetgPriceCalculatedFormatter extends PriceCalculatedFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'default_text' => 'Inscripción gratuita',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['default_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Default text'),
      '#default_value' => $this->getSetting('default_text'),
      '#description' => $this->t('Default text if the price = 0.'),
      '#maxlength' => '255',
    );

    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $settings = $this->getSettings();
    if (!empty($elements) && !empty($settings['default_text'])) {

      $context = new Context($this->currentUser, $this->currentStore->getStore(), NULL, [
        'field_name' => $items->getName(),
      ]);
      /** @var \Drupal\commerce\PurchasableEntityInterface $purchasable_entity */
      $purchasable_entity = $items->getEntity();
      $adjustment_types = array_filter($this->getSetting('adjustment_types'));
      $result = $this->priceCalculator->calculate($purchasable_entity, 1, $context, $adjustment_types);
      $calculated_price = $result->getCalculatedPrice();
      $number = $calculated_price->getNumber();

      if ($number == 0) {
        $elements[0]['#calculated_price'] = $this->t($settings['default_text']);
      }
    }

    return $elements;
  }

}
