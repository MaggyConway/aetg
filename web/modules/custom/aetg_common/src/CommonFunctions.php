<?php

namespace Drupal\aetg_common;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\NodeInterface;

/**
 * Class CommonFunctions.
 */
class CommonFunctions {

  /**
   * Checks if this event starts earlier than in this interval.
   *
   * @param NodeInterface $event
   *   The date of birth.
   * @param int $interval
   *   Number of minutes to validation.
   *
   * @return bool
   *   Return boll value.
   */
  public function lateForEvent(NodeInterface $event, $interval = 60) {
    if ($event->hasField('field_date_range') && !$event->get('field_date_range')->isEmpty()) {

      $start_date = new DrupalDateTime($event->get('field_date_range')->getValue()[0]['value'], 'GMT');
      $current_date = new DrupalDateTime('now');
      // If there is less than $interval minutes left before the start of the event or it has already passed.
      return ($start_date->getTimestamp() - $current_date->getTimestamp() < $interval * 60);
    }

    return TRUE;
  }

}
