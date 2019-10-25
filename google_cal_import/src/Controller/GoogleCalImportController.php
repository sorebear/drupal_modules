<?php

namespace Drupal\google_cal_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use ZCiCal;

require_once(drupal_get_path('module', 'google_cal_import') . '/src/ical_parser/zapcallib.php');

/**
 * Default controller for the GoogleCal Import module
 */
class GoogleCalImportController extends ControllerBase {
  protected static $existingEvents = [];
  protected static $icalEvents = [];
  protected static $icalobjs = [];

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
  public static function getGoogleCalendar() {
    $config = \Drupal::config('google_cal_import.settings');

    for ($i = 1; $i <= 10; $i++) {
      $url = $config->get('feed_url_' . $i);
      if (!empty($url)) {
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
        self::$icalobjs[] = [
          'data' => new ZCiCal($icalstring),
          'cal_category' => $config->get('should_map_category_' . $i) ? $config->get('taxonomy_term_' . $i) : NULL,
        ];
      }
    }
  }

  /**
   * Get the pertinent information from each event in iCal object
   * If the event exists, then update the corresponding node
   * If the event doesn't exist, create a new node 
   */
  public static function syncCalendar() {
    foreach (self::$icalobjs as $icalobj) {
      $eventsToCreate = [];
      $eventsToUpdate = [];
  
      $default_timezone = $icalobj['data']->tree->data['X-WR-TIMEZONE']->value['0'];
  
      foreach ($icalobj['data']->tree->child as $icalNode) {
        if ($icalNode->name == 'VEVENT') {
          $start_date = new DrupalDateTime($icalNode->data['DTSTART']->value['0'], $default_timezone);
          $end_date = new DrupalDateTime($icalNode->data['DTEND']->value['0'], $default_timezone);
          $diff = $start_date->diff($end_date);
  
          // Google calendar stores an all day event with no time and 1 day difference between
          // the start and end times.
          // The date recur module stores an all day event as 00:00:00 to 23:59:59 of the same day.
          // This code changes the format to match what Drupal is anticipating
          if ($diff->d != 0 && $diff->h == 0 && $diff->i == 0) {
            $end_date = $end_date->getPhpDateTime()->modify('-1 minutes')->format('Y-m-d\TH:i:s');
            $end_date = new DrupalDateTime($end_date);
          }

          $event = array();

          // Categories can be added to events by using special syntax: {{ category: tag1, tag2 }}
          // This code checks for tagged categories, removes them from the displayed body text,
          // then gets the taxonomy id and adds it to the event.
          $body_text_full = join(',', $icalNode->data['DESCRIPTION']->value);
          $category_matches = preg_match('/(?:\{\{\ ?category:\ ?)(.*)(?:\}\})/', $body_text_full, $matches);
          $body_text_parsed = preg_replace('/\{\{\ ?category:\ ?.*\}\}/', '', $body_text_full);
          $event['body'] = $body_text_parsed;

          if ($category_matches && isset($matches[1])) {
            $category_arr = explode(',', $matches[1]);
            $taxonomy_manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
            foreach ($category_arr as $category) {
              $category = trim($category);
              $term_array = $taxonomy_manager->loadByProperties(['name' => $category]);
              foreach ($term_array as $tid => $term_content) {
                $event['field_category'][] = $tid;
              }
            }
          }

          if ($icalobj['cal_category'] !== NULL) {
            $event['field_category'][] = $icalobj['cal_category'];
          }

          $event['type'] = 'events';
          $event['title'] = join(',', $icalNode->data['SUMMARY']->value);
          $event['langcode'] = 'en';
          $event['status'] = 1;
          $event['field_google_cal_uid'] = $icalNode->data['UID']->value['0'];
          $event['field_event_location'] = join(',', $icalNode->data['LOCATION']->value);
          $event['field_recurring_event_date'] = [[
            'value' => $start_date->format('Y-m-d\TH:i:s', ['timezone' => 'UTC']),
            'end_value' => $end_date->format('Y-m-d\TH:i:s', ['timezone' => 'UTC']),
            'rrule' => isset($icalNode->data['RRULE']) ? $icalNode->data['RRULE']->value['0'] : NULL,
            'timezone' => $default_timezone,
            'infinite' => FALSE,
          ]];
  
          self::$icalEvents[$icalNode->data['UID']->value['0']] = $event;
  
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
        $node->save();
      }
  
      // Update existing Event Nodes
      foreach ($eventsToUpdate as $uid => $eventData) {
        $eventToUpdate = self::$existingEvents[$uid];
        foreach ($eventData as $key => $value) {
          $eventToUpdate->set($key, $value);
        }
  
        $eventToUpdate->save();
      }
    }

  }

  /**
   * Find all nodes created by a Google Calendar import that are
   * no longer present on the Google Calendar and delete them.
   */
  public static function deleteRemovedEvents() {
    foreach (self::$existingEvents as $uid => $existingEvent) {
      if (!isset(self::$icalEvents[$uid])) {
        $existingEvent->delete();
      }
    }
  }
}