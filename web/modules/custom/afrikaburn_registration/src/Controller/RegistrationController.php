<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_registration\RegistrationController.
 */

namespace Drupal\afrikaburn_registration\Controller;


use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class RegistrationController extends ControllerBase {

  /**
   * Copy an archived registration.
   */
  public static function reuse($nid = FALSE) {
    if ($nid){

      $source = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $destination = $source->createDuplicate();
      $destination->setPublished(FALSE);
      $destination->set('created', time());
      $destination->set('changed', time());
      $destination->save();

      drupal_set_message('"' . $destination->get('title')->value . '" registration has been created as a draft. Please review the information and click "Save" when you are ready to submit the registration.');

      $redirect = new RedirectResponse('/node/' . $destination->id() .'/edit/form_1');
      $redirect->send();
    }
  }

  /**
   * Update a registration field.
   */
  public static function update($nid = FALSE){
    if ($nid){

      $target = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $field = \Drupal::request()->get('field');
      $value = \Drupal::request()->get('value');

      preg_match('/(?<name>[^\[]+)\[(?<value>[^\[]+)]/', $field, $parts);

      switch (TRUE){

        // Single Checkbox
        case $parts['value'] == 'value':
          $target->set($parts['name'], $value);
        break;

        // Multiple checkboxes
        case count($parts) && $parts['value'] != 'value':
          $storage = $target->get($parts['name']);
          $index = array_search(['value' => $parts['value']], $storage->getValue());
          if ($index !== FALSE && !$value) $storage->removeItem($index);
          if ($index === FALSE && $value) $storage->appendItem($parts['value']);
        break;

        // Other
        case count($parts) == 0:
          if ($value == '_none') $value = NULL;
          $target->set($field, $value);
        break;
      }

      $target->save();

      return new JsonResponse([], 200);
    }
  }
}
