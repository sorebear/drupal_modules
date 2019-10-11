<?php

namespace Drupal\recurring_event\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RecurringEventSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recurring_event_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['recurring_event.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('recurring_event.settings');

    $date_format = 'Y-m-d';
    $time_format = 'H:i';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Title'),
      '#default_value' => $config->get('title') ?: $this->t(''),
    ];

    $form['start_date'] = [
      '#type'  => 'datetime',
      '#title' => $this->t('State Date'),
      '#date_date_format' => $date_format,
      '#date_time_format' => $time_format,
      '#description' => date($date_format, time()),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('recurring_event.settings');
    $values = $form_state->getValues();

    $config->set('title', $values['title']);
    $config->set('start_date', $values['start_date']);
    $config->save();

    parent::submitForm($form, $form_state);
  }
}