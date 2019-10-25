<?php

namespace Drupal\google_cal_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
    
    $taxonomy_term_options = array();

    $vocabs = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    foreach ($vocabs as $vocab) {
      $taxonomy_term_options[$vocab->id()] = array();
      $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', $vocab->id())->execute();
      $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);

      foreach ($terms as $term) {
        $taxonomy_term_options[$vocab->id()][$term->id()] = t($term->getName());
      }
    }
  
    $form['instructions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Instructions for Adding Categories'),
      '#description' => $this->t("You can add categories to individual events by adding the following syntax into the calendar's description: {{ category: tag_1, tag_2, tag_3 }}. Example: {{ category: Music, Parks and Rec }}"),
    );

    $form['feed_1'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Feed 1'),
    );

    $form['feed_1']['feed_name_1'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Calendar Name'),
      '#description' => $this->t('The calendar name is not required and is just for personal reference.'),
      '#default_value' => $config->get('feed_name_1') ?: $this->t(''),
    );

    $form['feed_1']['feed_url_1'] = array(
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#title' => $this->t('Calendar Url'),
      '#description' => $this->t('Get this url by going to your Google Calendar settings and finding "Secret address in iCal format"'),
      '#default_value' => $config->get('feed_url_1') ?: $this->t(''),
    );

    $form['feed_1']['should_map_category_1'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Map calendar to taxonomy term'),
      '#default_value' => $config->get('should_map_category_1') ?: FALSE,
    );

    $form['feed_1']['taxonomy_term_1'] = array(
      '#type' => 'select',
      '#title' => $this->t('Taxonomy Term'),
      '#options' => $taxonomy_term_options,
      '#default_value' => $config->get('taxonomy_term_1') ?: NULL,
      '#description' => $this->t('All events in this calendar will be tagged with the selected taxonomy term'),
      '#states' => [
        'visible' => [
          'input[name="should_map_category_1"]' => ['checked' => TRUE]
        ]
      ]
    );

    $form['feed_2'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Feed 2'),
      '#states' => [
        'invisible' => [
          'input[name="feed_url_1"]' => ['value' => '']
        ]
      ]
    );

    $form['feed_2']['feed_name_2'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Calendar Name'),
      '#description' => $this->t('The calendar name is not required and is just for personal reference.'),
      '#default_value' => $config->get('feed_name_2') ?: $this->t(''),
    );

    $form['feed_2']['feed_url_2'] = array(
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#title' => $this->t('Calendar Url'),
      '#description' => $this->t('Get this url by going to your Google Calendar settings and finding "Secret address in iCal format"'),
      '#default_value' => $config->get('feed_url_2') ?: $this->t(''),
    );

    $form['feed_2']['should_map_category_2'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Map calendar to taxonomy term'),
      '#default_value' => $config->get('should_map_category_2') ?: FALSE,
    );

    $form['feed_2']['taxonomy_term_2'] = array(
      '#type' => 'select',
      '#title' => $this->t('Taxonomy Term'),
      '#options' => $taxonomy_term_options,
      '#default_value' => $config->get('taxonomy_term_2') ?: NULL,
      '#description' => $this->t('All events in this calendar will be tagged with the selected taxonomy term'),
      '#states' => [
        'visible' => [
          'input[name="should_map_category_2"]' => ['checked' => TRUE]
        ]
      ]
    );

    $form['feed_3'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Feed 3'),
      '#states' => [
        'invisible' => [
          'input[name="feed_url_2"]' => ['value' => '']
        ]
      ]
    );

    $form['feed_3']['feed_name_3'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Calendar Name'),
      '#description' => $this->t('The calendar name is not required and is just for personal reference.'),
      '#default_value' => $config->get('feed_name_3') ?: $this->t(''),
    );

    $form['feed_3']['feed_url_3'] = array(
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#title' => $this->t('Calendar Url'),
      '#description' => $this->t('Get this url by going to your Google Calendar settings and finding "Secret address in iCal format"'),
      '#default_value' => $config->get('feed_url_3') ?: $this->t(''),
    );

    $form['feed_3']['should_map_category_3'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Map calendar to taxonomy term'),
      '#default_value' => $config->get('should_map_category_3') ?: FALSE,
    );

    $form['feed_3']['taxonomy_term_3'] = array(
      '#type' => 'select',
      '#title' => $this->t('Taxonomy Term'),
      '#options' => $taxonomy_term_options,
      '#default_value' => $config->get('taxonomy_term_3') ?: NULL,
      '#description' => $this->t('All events in this calendar will be tagged with the selected taxonomy term'),
      '#states' => [
        'visible' => [
          'input[name="should_map_category_3"]' => ['checked' => TRUE]
        ]
      ]
    );

    $form['feed_4'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Feed 4'),
      '#states' => [
        'invisible' => [
          'input[name="feed_url_3"]' => ['value' => '']
        ]
      ]
    );

    $form['feed_4']['feed_name_4'] = array(
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#title' => $this->t('Calendar Name'),
      '#description' => $this->t('The calendar name is not required and is just for personal reference.'),
      '#default_value' => $config->get('feed_name_4') ?: $this->t(''),
    );

    $form['feed_4']['feed_url_4'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Calendar Url'),
      '#description' => $this->t('Get this url by going to your Google Calendar settings and finding "Secret address in iCal format"'),
      '#default_value' => $config->get('feed_url_4') ?: $this->t(''),
    );

    $form['feed_4']['should_map_category_4'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Map calendar to taxonomy term'),
      '#default_value' => $config->get('should_map_category_4') ?: FALSE,
    );

    $form['feed_4']['taxonomy_term_4'] = array(
      '#type' => 'select',
      '#title' => $this->t('Taxonomy Term'),
      '#options' => $taxonomy_term_options,
      '#default_value' => $config->get('taxonomy_term_4') ?: NULL,
      '#description' => $this->t('All events in this calendar will be tagged with the selected taxonomy term'),
      '#states' => [
        'visible' => [
          'input[name="should_map_category_4"]' => ['checked' => TRUE]
        ]
      ]
    );

    $form['feed_5'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Feed 5'),
      '#states' => [
        'invisible' => [
          'input[name="feed_url_4"]' => ['value' => '']
        ]
      ]
    );

    $form['feed_5']['feed_name_5'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Calendar Name'),
      '#description' => $this->t('The calendar name is not required and is just for personal reference.'),
      '#default_value' => $config->get('feed_name_5') ?: $this->t(''),
    );

    $form['feed_5']['feed_url_5'] = array(
      '#type' => 'textfield',
      '#maxlength' => 300,
      '#title' => $this->t('Calendar Url'),
      '#description' => $this->t('Get this url by going to your Google Calendar settings and finding "Secret address in iCal format"'),
      '#default_value' => $config->get('feed_url_5') ?: $this->t(''),
    );

    $form['feed_5']['should_map_category_5'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Map calendar to taxonomy term'),
      '#default_value' => $config->get('should_map_category_5') ?: FALSE,
    );

    $form['feed_5']['taxonomy_term_5'] = array(
      '#type' => 'select',
      '#title' => $this->t('Taxonomy Term'),
      '#options' => $taxonomy_term_options,
      '#default_value' => $config->get('taxonomy_term_5') ?: NULL,
      '#description' => $this->t('All events in this calendar will be tagged with the selected taxonomy term'),
      '#states' => [
        'visible' => [
          'input[name="should_map_category_5"]' => ['checked' => TRUE]
        ]
      ]
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the iCal string from the URL
    $config = $this->config('google_cal_import.settings');
    $values = $form_state->getValues();
    for ($i = 1; $i <= 5; $i++) {
      $config->set('feed_name_' . $i, $values['feed_name_' . $i]);
      $config->set('feed_url_' . $i, $values['feed_url_' . $i]);
      $config->set('should_map_category_' . $i, $values['should_map_category_' . $i]);
      $config->set('taxonomy_term_' . $i, $values['taxonomy_term_' . $i]);
    }
  
    $config->save();

    drupal_set_message(t('Configuration saved.'));
  }
}
