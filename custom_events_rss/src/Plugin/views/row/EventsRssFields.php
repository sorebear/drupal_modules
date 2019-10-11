<?php

namespace Drupal\custom_events_rss\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\row\RssFields;

/**
 * View row plugin to render a Custom Events RSS item based on fields.
 *
 * @ViewsRow(
 *   id = "google_news_rss_fields",
 *   title = @Translation("Custom Events Fields"),
 *   help = @Translation("Custom RSS items for Santa Ana Events."),
 *   theme = "views_view_row_rss_custom_events_rss",
 *   display_types = {"feed"}
 * )
 */
class EventsRssFields extends RssFields {

  /**
   * Does the row plugin support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  protected $distinctValues = [];

  /**
   * Define the available options.
   *
   * @return array
   *   The array with options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['publication_date_field'] = ['default' => ''];
    $options['title_field'] = ['default' => ''];
    $options['start_date'] = ['default' => ''];
    $options['start_time'] = ['default' => ''];
    $options['end_time'] = ['default' => ''];
    return $options;
  }

  /**
   * Options form for Google News rss feed.
   *
   * @param array $form
   *   The form to build.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state and values.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Remove the fields that are not needed for a Google News sitemap.
    unset(
      $form['title_field'],
      $form['description_field'],
      $form['creator_field'],
      $form['date_field'],
      $form['guid_field_options']
    );

    // Set the initial labels for the form fields.
    $initial_labels = ['' => $this->t('- None -')];
    $view_fields_labels = $this->displayHandler->getFieldLabels();
    $view_fields_labels = array_merge($initial_labels, $view_fields_labels);

    $form['link_field']['#description'] = $this->t('Absolute URL to node.');
    
    $form['title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Title field'),
      '#description' => $this->t('The field that contains the title of the node.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['title_field'],
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'select',
      '#title' => $this->t('Description field'),
      '#description' => $this->t('Typically this will be set to a trimmed version of the body'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['description'],
    ];

    $form['start_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event date field'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['start_date'],
      '#required' => TRUE,
    ];

    $form['start_time'] = [
      '#type' => 'select',
      '#title' => $this->t('Event start time field'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['start_time'],
      '#required' => TRUE,
    ];

    $form['end_time'] = [
      '#type' => 'select',
      '#title' => $this->t('Event end time field'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['end_time'],
    ];
  }

  /**
   * Validate the Google News RSS settings.
   *
   * @return array
   *   Array with errors, if any.
   */
  public function validate() {
    $errors = [];
    // Only title, name and date are mandatory.
    $required_options = [
      'link_field',
      'title_field',
      'start_date',
      'start_time',
      'end_time',
    ];
    foreach ($required_options as $required_option) {
      if (empty($this->options[$required_option])) {
        $errors[] = $this->t('Not all required fields were filled in (Google News RSS fields).');
        break;
      }
    }
    return $errors;
  }

  /**
   * Render the RSS feed.
   *
   * @param object $row
   *   Current row to render.
   *
   * @return array
   *   Render array.
   */
  public function render($row) {
    static $row_index;

    // Reset the row index to zero if it has not been set.
    if (!isset($row_index)) {
      $row_index = 0;
    }

    // Create the RSS item object.
    $item = new \stdClass();

    // Add the required elements from the current row.
    $item->elements = [
      [
        'key' => 'link',
        'value' => $this->getField($row_index, $this->options['link_field']),
      ],
      [
        'key' => 'title',
        'value' => htmlentities($this->getField($row_index, $this->options['title_field']), ENT_QUOTES, 'UTF-8'),
      ],
      [
        'key' => 'eventDate',
        'value' => trim(explode('-', $this->getField($row_index, $this->options['start_date']))[0]),
      ],
      [
        'key' => 'eventTime',
        'value' => trim(explode('-', $this->getField($row_index, $this->options['start_time']))[0]),
      ],
    ];

    // For the non-required fields, first check if they exist and then add them
    // to the elements as well.
    if ($this->options['end_time'] !== FALSE) {
      // Also check if it's not empty, to prevent empty elements in the sitemap.
      $date_range_arr = explode('-', $this->getField($row_index, $this->options['end_time']));
      if (isset($date_range_arr[1]) && strlen($date_range_arr[1]) > 0) {
        $item->elements[] = [
          'key' => 'eventEndTime',
          'value' => trim($date_range_arr[1]),
        ];
      }
    }

    if ($this->options['description'] !== FALSE) {
      // Also check if it's not empty, to prevent empty elements in the sitemap.
      $description = $this->getField($row_index, $this->options['description']);

      if (strlen($description) > 0) {
        $item->elements[] = [
          'key' => 'description',
          'value' => htmlentities($description, ENT_QUOTES, 'UTF-8')
        ];
      }
    }

    // Increase the row index by one after each row.
    $row_index++;

    // Add the required namespaces.
    $this->view->style_plugin->namespaces = [
      'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
    ];

    // Create the build array and return it.
    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];
    return $build;

  }

}
