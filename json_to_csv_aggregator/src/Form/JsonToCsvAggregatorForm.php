<?php

namespace Drupal\json_to_csv_aggregator\Form;

use Drupal\Core\Form\ConfigBaseForm;
use Drupal\Core\Form\FormStateInterface;

class JsonToCsvAggregatorForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'json_to_csv_aggregator_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['json_to_csv_aggregator.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('json_to_csv_aggregator.settings');
    $form['json_export_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('JSON Export Url'),
      '#description' => $this->t('The url of a JSON feed. This module will consume that feed and create a CSV from it.'),
      '#default_value' => $config->get('json_export_url') ?: $this->t(''),
    ];

    $form['field_to_aggregate'] = [
      '#type'  => 'textfield',
      '#title' => $this->t('Aggregate Field'),
      '#description' => $this->t('Pick the field name to aggregate.'),
      '#default_value' => $config->get('field_to_aggregate') ?: $this->t(''),
    ];

    $form['type_of_aggregation'] = [
      '#type'  => 'select',
      '#title' => $this->t('Type of aggregation.'),
      '#options' => [
        'sum' => 'Sum',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('json_to_csv_aggregator.settings');
    $values = $form_state->getValues();
    $config->set('json_export_url', $values['json_export_url']);
    $config->set('field_to_aggregate', $values['field_to_aggregate']);
    $config->set('type_of_aggregation', $values['type_of_aggregation']);
    $config->save();
    parent::submitForm($form, $form_state);
  }
}