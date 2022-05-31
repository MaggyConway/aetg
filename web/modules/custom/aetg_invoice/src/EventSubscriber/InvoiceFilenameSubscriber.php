<?php

namespace Drupal\aetg_invoice\EventSubscriber;

use Drupal\commerce_invoice\Event\InvoiceEvents;
use Drupal\commerce_invoice\Event\InvoiceFilenameEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceFilenameSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      InvoiceEvents::INVOICE_FILENAME => 'alterFilename',
    ];
  }

  /**
   * Alters the invoice filename.
   *
   * @param \Drupal\commerce_invoice\Event\InvoiceFilenameEvent $event
   *   The transition event.
   */
  public function alterFilename(InvoiceFilenameEvent $event) {
    $filename_array = explode('-', $event->getFilename(), 2);

    if (!empty($filename_array[0])) {
      $event->setFilename($filename_array[0]);
    }
  }

}
