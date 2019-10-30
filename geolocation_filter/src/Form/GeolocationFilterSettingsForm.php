<?php

namespace Drupal\geolocation_filter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class GeolocationFilterSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geolocation_filter_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['geolocation_filter.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('geolocation_filter.settings');

    $form['blocked_countries'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Blocked Countries'),
      '#description' => $this->t('Add the 2 letter country code for each country you would like blocked. Seperate each entry with a comma ",". You can find country codes at <a href="@link" target="_blank">Nations Online</a>.', array('@link' => 'https://nationsonline.org/oneworld/country_code_list.htm')),
      '#default_value' => $config->get('blocked_contries') ?: $this->t("CN, KP, MX, RU"),
    ];

    $form['blocked_ip_addresses'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Blocked IP Addresses'),
      '#description' => $this->t('Add specific IP addresses to block. Seperate each entry with a comma ","'),
      '#default_value' => $config->get('blocked_ip_addresses') ?: $this->t(''),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('geolocation_filter.settings');
    $values = $form_state->getValues();

    $config->set('blocked_countries', $values['blocked_countries']);
    $config->set('blocked_ip_addresses', $values['blocked_ip_addresses']);
    $config->save();

    parent::submitForm($form, $form_state);
  }
}