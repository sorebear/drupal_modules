<?php

namespace Drupal\google_cal_import\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the GoogleCal Import module
 */
class GoogleCalImportController extends ControllerBase {
  /**
   * Initiate Batch process for Sync Calendar
   */
  public static function syncCalendar($icalobj, &$context) {
    
  }

  public static function syncComplete($success, $results, $operations) {
    if ($success) {
      $success_message = t("Google Cal Synchornization Complete");
      drupal_set_message($success_message);
    }  else {
      $error_operation = reset($operations);
      $error_message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      drupal_set_message($error_message); 
    }
  }
}