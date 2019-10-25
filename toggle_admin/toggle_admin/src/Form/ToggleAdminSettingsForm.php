<?php

namespace Drupal\toggle_admin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ToggleAdminSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toggle_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['toggle_admin.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('toggle_admin.settings');

    $form['toggle_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Toggle Controls'),
      '#description' => $this->t('This enables or disables the module functionality.'),
      '#default_value' => $config->get('toggle_enabled') ?: FALSE,
    ];

    $form['default_on'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Admin Menu By Default'),
      '#description' => $this->t('Should the admin menu be shown by default on initial page load.'),
      '#default_value' => $config->get('default_on') ?: FALSE,
    ];

    $form['enable_for_users'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Specific User List'),
      '#description' => $this->t('Include only the users who should see the toggle admin controls. Seperate different users with a ",". If left blank, it will be enabled for all authenticated users.'),
      '#default_value' => $config->get('enable_for_users') ?: $this->t(''),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('toggle_admin.settings');
    $values = $form_state->getValues();

    $config->set('toggle_enabled', $values['toggle_enabled']);
    $config->set('default_on', $values['default_on']);
    $config->set('enable_for_users', $values['enable_for_users']);
    $config->save();

    parent::submitForm($form, $form_state);
  }
}