<?php

namespace Drupal\google_cal_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\date_recur\Event\DateRecurValueEvent;
use ZCiCal;


// require_once('/Users/soren/Desktop/Code/drupal_playground/web/modules/custom/google_cal_import/src/ical_parser/zapcallib.php');
require_once(drupal_get_path('module', 'google_cal_import') . '/src/ical_parser/zapcallib.php');

class GoogleCalImportForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_cal_import_form';
  }
 
  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['google_cal_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_cal_import.settings');

    $form['feed_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Feed Url'),
      '#default_value' => $config->get('feed_url') ?: $this->t(''),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get Current Event Nodes
    $node_stoarge = \Drupal::entityTypeManager()->getStorage('node');
    $eventNids = \Drupal::entityQuery('node')->condition('type', 'events')->execute();
    $eventNodes = $node_stoarge->loadMultiple($eventNids);

    // kint($eventNodes['5']);
    // kint($eventNodes['5']->get('field_event_date'));
    // kint($eventNodes['5']->get('field_event_date')->getValue());

    // See which nodes are from Google Calendar
    // Put them in an array, indexed by the uid
    $existingEvents = array();
    foreach ($eventNodes as $eventNode) {
      $googleCalUid = $eventNode->get('field_google_cal_uid')->getValue();
      if ($googleCalUid) {
        $existingEvents[$googleCalUid['0']['value']] = $eventNode;
      }
    }

    kint($existingEvents);
    kint($existingEvents['1b8i0dghh386cpi6fgmudrjrod@google.com']->get('field_event_date'));
    kint($existingEvents['2pf334hpj30db5ev54lu207qe3@google.com']->get('field_event_date'));

    // Get the iCal string from the URL
    $config = $this->config('google_cal_import.settings');
    $values = $form_state->getValues();
    $config->set('feed_url', $values['feed_url']);
    $config->save();
    $url = $values['feed_url'];
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
    $icalobj = new ZCiCal($icalstring);

    $eventsToCreate = [];
    $eventsToUpdate = [];

    $default_timezone = $icalobj->tree->data['X-WR-TIMEZONE']->value['0'];

    // Get the pertinent information from each event in the iCal object
    foreach ($icalobj->tree->child as $icalNode) {
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
        if ($existingEvents[$icalNode->data['UID']->value['0']]) {
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
      $eventToUpdate = $existingEvents[$uid];
      foreach ($eventData as $key => $value) {
        $eventToUpdate->set($key, $value);
      }

      $eventToUpdate->save();
    }

    $batch = [
      'title' => t('Synching Calendar'),
      'operations' => [
        [
          '\Drupal\google_cal_import\Controller\GoogleCalImportController::syncCalendar',
          [$icalobj],
        ]
      ],
      'finished' => '\Drupal\google_cal_import\Controller\GoogleCalImportController::syncComplete',
      'init_message' => t('Initializing GoogleCal sync'),
      'progress_message' => t('Processed @current of @total.'),
      'error_message' => t('There was an issue syncing your calendar'),
    ];

    batch_set($batch);
  }
}