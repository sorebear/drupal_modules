<?php

namespace Drupal\google_cal_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\google_cal_import\Ics;

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

    $form['feed_file'] = array(
      '#type' => 'file',
      '#title' => $this->t('Feed File'),
      '#default_value' => $config->get('feed_file') ?: $this->t(''),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $file = NULL;

    if (strlen($_FILES['files']['name']['feed_file']) > 0) {
      echo 'Get File';
      $file = $_FILES['files']['name']['feed_file'];
    } else {
      echo 'Get Url';
      $file = $values['feed_url'];
    }

    if (!$file) {
      return;
    }

    $ics = new Ics();
    $icsEvents = $ics->getIcsEventsAsArray($file);

    kint($icsEvents);

    $TZID = '';

    foreach($icsEvents as $event) {
      echo 'In Event Loop<br>';
      if ($event['BEGIN'] == "VTIMEZONE\r") {
        $TZID = $event['TZID'];
        echo 'Set Timezone<br>';
      } else if ($event['BEGIN'] == "VEVENT\r") {

        if (isset($event['DTSTART;VALUE=DATE'])) {
          echo 'Start Single Event<br>';
        } else {
          echo 'Start Repeating Event ' . $event['DTSTART;TZID=' . $TZID] . '<br>';
        }
      }
    }

    kill();

    parent::submitForm($form, $form_state);
  }
}