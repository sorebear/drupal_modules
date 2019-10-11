<?php

namespace Drupal\civic_carousel\Plugin\views\style;

use Drupal\core\form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin for Civic Carousels
 * 
 * @ingroup views_style_plugins
 * 
 * @viewsStyle(
 *  id = "civic_carousel",
 *  title = @Translation("Civic Carousel"),
 *  help = @Translation("Add a Carousel option for View Formats"),
 *  theme = "views_view_civic_carousel",
 *  display_types = { "normal" }
 * )
 */
class CivicCarousel extends StylePluginBase {

  /**
   * Does this Style plugin allow Row plugins?
   * 
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the Style plugin support grouping of rows?
   * 
   * @var bool
   */
    protected $usesGrouping = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // $options['sm_num_of_items'] = array('default' => '');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['sm_num_of_items'] = array(
      '#type' => 'number',
      '#title' => $this->t('Num of Carousel Items at Small Display'),
      '#default_value' => (isset($this->options['sm_num_of_items'])) ? $this->options['sm_num_of_items'] : 1,
    );

    $form['md_num_of_items'] = array(
      '#type' => 'number',
      '#title' => $this->t('Num of Carousel Items at Medium Display'),
      '#default_value' => (isset($this->options['md_num_of_items'])) ? $this->options['md_num_of_items'] : 1,
    );

    $form['lg_num_of_items'] = array(
      '#type' => 'number',
      '#title' => $this->t('Num of Carousel Items at Large Display'),
      '#default_value' => (isset($this->options['lg_num_of_items'])) ? $this->options['lg_num_of_items'] : 1,
    );

    $form['xl_num_of_items'] = array(
      '#type' => 'number',
      '#title' => $this->t('Num of Carousel Items at Extra Large Display'),
      '#default_value' => (isset($this->options['xl_num_of_items'])) ? $this->options['xl_num_of_items'] : 1,
    );

    $form['infinite'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Infinite Scrolling'),
      '#description' => $this->t('If selected, the list of items will be repeated everytime the end is reached.'),
      '#default_value' => (isset($this->options['infinite'])) ? $this->options['infinite'] : FALSE,
    );

    // Extra CSS classes
    $form['classes'] = array(
      '#type' => 'textfield',
      '#title' => t('Extra CSS classes'),
      '#default_value' => (isset($this->options['classes'])) ? $this->options['classes'] : 'civic-carousel',
    );
  }
}