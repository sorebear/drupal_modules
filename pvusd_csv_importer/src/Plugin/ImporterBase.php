<?php

namespace Drupal\pvusd_csv_importer\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\pvusd_csv_importer\Annotation\Importer
 * @see \Drupal\pvusd_csv_importer\Plugin\ImporterManager
 * @see \Drupal\pvusd_csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */
abstract class ImporterBase extends PluginBase implements ImporterInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs ImporterBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function data() {
    $csv = $this->configuration['csv'];
    $return = [];

    if ($csv && is_array($csv)) {
      $csv_fields = $csv[0];
      unset($csv[0]);
      foreach ($csv as $index => $data) {
        foreach ($data as $key => $content) {
          if ($content && isset($csv_fields[$key])) {
            $content = Unicode::convertToUtf8($content, mb_detect_encoding($content));
            $fields = explode('|', $csv_fields[$key]);

            if ($fields[0] == 'translation') {
              if (count($fields) > 3) {
                $return['translations'][$index][$fields[3]][$fields[1]][$fields[2]] = $content;
              }
              else {
                $return['translations'][$index][$fields[2]][$fields[1]] = $content;
              }
            }
            else {
              $field = $fields[0];
              if (count($fields) > 1) {
                foreach ($fields as $key => $in) {
                  $return['content'][$index][$field][$in] = $content;
                }
              }
              else if (isset($return['content'][$index][$field])) {
                $prev = $return['content'][$index][$field];
                $return['content'][$index][$field] = [];

                if (is_array($prev)) {
                  $prev[] = $content;
                  $return['content'][$index][$field] = $prev;
                }
                else {
                  $return['content'][$index][$field][] = $prev;
                  $return['content'][$index][$field][] = $content;
                }
              }
              else {
                $return['content'][$index][current($fields)] = $content;
              }
            }
          }
        }

        if (isset($return[$index])) {
          $return['content'][$index] = array_intersect_key($return[$index], array_flip($this->configuration['fields']));
        }
      }
    }

    return $return;
  }

  private function getLocationTaxonomyId($location) {
    $punctuation = array(".", ",");
    $seperators = array("-", "_");
    $location = strtoupper($location);
    $location = str_replace($punctuation, "", $location);
    $location = str_replace($seperators, " ", $location);
    
    $taxonomyIdMap = array(
      'AIRE LIBRE ES' => '33',
      'ARROWHEAD ES' => '34',
      'ASSESSMENT' => '35',
      'BOULDER CREEK ES' => '36',
      'CACTUS VIEW ES' => '37',
      'CAMPO BELLO ES' => '38',
      'CHOLLA COMPLEX' => '39',
      'COMMUNITY RESOURCE CENTER' => '40',
      'COPPER CANYON ES' => '41',
      'CURRICULUM' => '42',
      'DESERT COVE ES' => '43',
      'DESERT SHADOWS ES' => '44',
      'DESERT SHADOWS MS' => '45',
      'DESERT SPRINGS PREPARATORY' => '46',
      'DESERT TRAILS ES' => '47',
      'EAGLE RIDGE ES' => '48',
      'ECHO MOUNTAIN INTERMEDIATE' => '49',
      'ECHO MOUNTAIN PRIMARY' => '50',
      'EXPLORER MS' => '51',
      'FACILITIES AND CONSTRUCTION' => '52',
      'FINANCE' => '53',
      'FINE ARTS' => '54',
      'FIRESIDE ES' => '55',
      'FOOTHILLS CAMPUS' => '56',
      'GIFTED EDUCATION' => '57',
      'GRAYHAWK ES' => '58',
      'GREENWAY MS' => '59',
      'HIDDEN HILLS ES' => '60',
      'HORIZON HS' => '61',
      'HUMAN RESOURCES' => '62',
      'INDIAN BEND ES' => '63',
      'LANGUAGE ACQUISITION' => '64',
      'LARKSPUR ES' => '65',
      'LEADERSHIP ELEMENTARY' => '66',
      'LEADERSHIP SECONDARY' => '67',
      'LIBERTY ES' => '68',
      'MARKETING AND COMMUNICATIONS' => '69',
      'MERCURY MINE ES' => '70',
      'MOUNTAIN TRAIL MS' => '71',
      'NORTH CANYON HS' => '72',
      'NORTH RANCH' => '73',
      'NUTRITION AND WELLNESS' => '74',
      'PALOMINO INTERMEDIATE' => '75',
      'PALOMINO PRIMARY' => '76',
      'PARADISE VALLEY' => '77',
      'PAYROLL' => '78',
      'PINNACLE HS' => '79',
      'PINNACLE PEAK PREPARATORY' => '80',
      'PROFESSIONAL DEVELOPMENT' => '81',
      'PURCHASING' => '82',
      'PVONLINE' => '83',
      'QUAIL RUN ES' => '84',
      'ROADRUNNER' => '85',
      'SANDPIPER ES' => '86',
      'SHADOW MOUNTAIN HS' => '87',
      'SHEA MS' => '88',
      'SONORAN SKY ES' => '89',
      'SPECIAL EDUCATION' => '90',
      'STORES INVENTORY' => '91',
      'SUNRISE MS' => '92',
      'SUNSET CANYON ES' => '93',
      'SUPERINTENDENTS OFFICE' => '94',
      'SWEETWATER COMMUNITY SCHOOL' => '95',
      'TECHNOLOGY' => '96',
      'TRANSPORTATION' => '97',
      'VISTA VERDE MS' => '98',
      'WHISPERING WIND ACADEMY' => '99',
      'WILDFIRE ES' => '100'
    );

    return $taxonomyIdMap[$location];
  }

  /**
   * {@inheritdoc}
   */
  public function add($content, array &$context) {
    if (!$content) {
      return NULL;
    }

    $entity_type = $this->configuration['entity_type'];
    $entity_type_bundle = $this->configuration['entity_type_bundle'];
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);

    $added = 0;
    $updated = 0;
    $test_log_1 = '';

    foreach ($content['content'] as $key => $data) {
      if ($entity_definition->hasKey('bundle') && $entity_type_bundle) {
        $data[$entity_definition->getKey('bundle')] = $this->configuration['entity_type_bundle'];
      }
      
      /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $entity_storage  */
      $entity_storage = $this->entityTypeManager->getStorage($this->configuration['entity_type']);

      try {
        // Remap the Data
        $data['title'] = $data['First name'] . ' ' . $data['Last name'];
        unset($data['First name']);
        unset($data['Last name']);
        $data['field_position'] = $data['Job title'];
        unset($data['Job title']);
        $data['field_staff_email'] = $data['Email address'];
        unset($data['Email address']);
        $data['field_staff_phone_number'] = $data['School phone'];
        unset($data['School phone']);
        $data['field_work_location'] = $this->getLocationTaxonomyId($data['Work location']);
        unset($data['Work location']);

        $duplicateEntities = $entity_storage->loadByProperties(['field_staff_email' => $data['field_staff_email']]);
        $duplicateEntity = reset($duplicateEntities);

        // if (isset($data[$entity_definition->getKeys()['id']]) && $entity = $entity_storage->load($data[$entity_definition->getKeys()['id']])) {
        if ($duplicateEntity !== FALSE) {
          /** @var \Drupal\Core\Entity\ContentEntityInterface $entity  */
          foreach ($data as $id => $set) {
            $duplicateEntity->set($id, $set);
          }

          $this->preSave($duplicateEntity, $data, $context);
  
          if ($duplicateEntity->save()) {
            $updated++;
          }
        }
        else {
          /** @var \Drupal\Core\Entity\ContentEntityInterface $entity  */
          $entity = $this->entityTypeManager->getStorage($this->configuration['entity_type'])->create($data);
          
          $this->preSave($entity, $data, $context);

          if ($entity->save()) {
            $added++;
          }
        }

        if (isset($content['translations'][$key]) && is_array($content['translations'][$key])) {
          foreach ($content['translations'][$key] as $code => $translations) {
            $entity_data = array_replace($translations, $translations);

            if ($entity->hasTranslation($code)) {
              $entity_translation = $entity->getTranslation($code);

              foreach ($entity_data as $key => $translation_data) {
                $entity_translation->set($key, $translation_data);
              } 
            }
            else {
              $entity_translation = $entity->addTranslation($code, $entity_data);
            }

            $entity_translation->save();
          }
        }
      }
      catch (\Exception $e) {
      }
    }

    $context['results'] = [$added, $updated, $test_log_1];
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations() {
    $operations[] = [
      [$this, 'add'],
      [$this->data()],
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function finished($success, $results, array $operations) {
    $message = '';

    if ($success) {
      $message = $this->t('@count_added content added and @count_updated updated, Test Logs: @test_logs', [
        '@count_added' => isset($results[0]) ? $results[0] : 0,
        '@count_updated' => isset($results[1]) ? $results[1] : 0,
        '@test_logs' => isset($results[2]) ? $results[2] : 'Empty Log'
      ]);
    }

    drupal_set_message($message);
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $process = [];
    if ($operations = $this->getOperations()) {
      $process['operations'] = $operations;
    }

    $process['finished'] = [$this, 'finished'];

    batch_set($process);
  }

  /**
   * Override entity before run Entity::save().
   *
   * @param mixed $entity
   *   The entity object.
   * @param array $content
   *   The content array to be saved.
   * @param array $context
   *   The batch context array.
   */
  public function preSave(&$entity, array $content, array &$context) {}

}
