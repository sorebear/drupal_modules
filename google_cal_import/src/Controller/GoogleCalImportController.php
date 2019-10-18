<?php

namespace Drupal\google_cal_import\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use ZCiCal;

require_once(drupal_get_path('module', 'google_cal_import') . '/src/ical_parser/zapcallib.php');

/**
 * Default controller for the GoogleCal Import module
 */
class GoogleCalImportController extends ControllerBase {
  protected static $existingEvents = [];
  protected static $icalobj;
  private static $countCreated = 0;
  private static $countUpdated = 0;
  private static $countDeleted = 0;

  /**
   * Get Existing Event Nodes
   */
  public static function getExistingEvents() {
    $node_stoarge = \Drupal::entityTypeManager()->getStorage('node');
    $eventNids = \Drupal::entityQuery('node')->condition('type', 'events')->execute();
    $eventNodes = $node_stoarge->loadMultiple($eventNids);

    foreach ($eventNodes as $eventNode) {
      $googleCalUid = $eventNode->get('field_google_cal_uid')->getValue();
      if ($googleCalUid) {
        self::$existingEvents[$googleCalUid['0']['value']] = $eventNode;
      }
    }
  }

  /**
   * Get the iCal string from the URL
   * Then create an iCal object using the ZCiCal Library
   */
  public static function getGoogleCalendar($url, &$context) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $icalstring = curl_exec($ch);
    curl_close($ch);

    if (!$icalstring) {
      return;
    }

    // Create an iCal object using the ZCiCal library
    self::$icalobj = new ZCiCal($icalstring);
  }

  /**
   * Get the pertinent information from each event in iCal object
   * If the event exists, then update the corresponding node
   * If the event doesn't exist, create a new node 
   */
  public static function syncCalendar() {
    $eventsToCreate = [];
    $eventsToUpdate = [];

    $default_timezone = self::$icalobj->tree->data['X-WR-TIMEZONE']->value['0'];

    foreach (self::$icalobj->tree->child as $icalNode) {
      if ($icalNode->name == 'VEVENT') {
        // kint($icalNode->data);

        $event = array();
        $event['type'] = 'events';
        $event['title'] = $icalNode->data['SUMMARY']->value;
        $event['body'] = $icalNode->data['DESCRIPTION']->value;
        $event['langcode'] = 'en';
        $event['status'] = 1;
        $event['field_google_cal_uid'] = $icalNode->data['UID']->value['0'];
        $event['field_location'] = join('', $icalNode->data['LOCATION']->value);
        // $event['field_event_date'] = [
          // 'value' => $icalNode->data['DTSTART']->value['0'],
          // 'end_value' => $icalNode->data['DTEND']->value['0'],
          // 'rrule' => $icalNode->data['RRULE'] ? $icalNode->data['RRULE']->value['0'] : NULL,
          // 'timezone' => $default_timezone,
          // 'infinite' => FALSE
          // 'Start date' => $icalNode->data['DTSTART']->value['0'],
          // 'End date' => $icalNode->data['DTEND']->value['0'],
          // 'rrule' => $icalNode->data['RRULE'] ? $icalNode->data['RRULE']->value['0'] : NULL,
        // ];

        // If the event exists, put the details in an array to be updated
        if (isset(self::$existingEvents[$icalNode->data['UID']->value['0']])) {
          $eventsToUpdate[$icalNode->data['UID']->value['0']] = $event;
        // If the event doesn't exist, put the details in an array to be created
        } else {
          $eventsToCreate[] = $event;
        }
      }
    }

    // Create new Event Nodes
    foreach ($eventsToCreate as $eventData) {
      $node = Node::create($eventData);
      if ($node->save()) {
        self::$countCreated = self::$countCreated + 1;
      }
    }

    // Update existing Event Nodes
    foreach ($eventsToUpdate as $uid => $eventData) {
      $eventToUpdate = self::$existingEvents[$uid];
      foreach ($eventData as $key => $value) {
        $eventToUpdate->set($key, $value);
      }

      if ($eventToUpdate->save()) {
        self::$countUpdated += 1;
      };
    }
  }

  public static function syncComplete($success, $results, $operations) {
    if ($success) {
      $success_message = t("Google Cal Synchornization Complete. @createdCount events created. @updatedCount events updated.", [
        '@updatedCount' => self::$countUpdated,
        '@createdCount' => self::$countCreated,
      ]);
      drupal_set_message($success_message);
    }  else {
      $error_operation = reset($operations);
      $error_message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      drupal_set_message($error_message); 
    }
  }
}