<?php

namespace Drupal\pvusd_csv_importer\Plugin\Importer;

use Drupal\pvusd_csv_importer\Plugin\ImporterBase;

/**
 * Class UserImporter.
 *
 * @Importer(
 *   id = "user_importer",
 *   entity_type = "user",
 *   label = @Translation("User importer")
 * )
 */
class UserImporter extends ImporterBase {}
