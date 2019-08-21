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
      $destination->set('field_prjr_complete', []);
      $destination->set('field_form_mode', NULL);
      $destination->set('field_prj_adm_review', NULL);
      $destination->save();

      drupal_set_message('"' . $destination->get('title')->value . '" registration has been created as a draft. Please review the information and click "Submit" when you are ready to submit the registration.');

      $redirect = new RedirectResponse('/node/' . $destination->id() .'/edit/form_1');
      $redirect->send();
    }
  }

  /**
   * Update a registration field.
   */
  public static function update($nid = FALSE){
    if ($nid){

      $field = \Drupal::request()->get('field');
      $target = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $value = \Drupal::request()->get('value');

      preg_match('/(?<name>[^\[]+)\[(?<value>[^\[]+)]/', $field, $parts);

      $field_name = count($parts)
        ? $parts['name']
        : str_replace('[]', '', $field);

      $definition = $target->get($field_name)->getFieldDefinition()->get('field_type');

      switch ($definition){

        // Multiple checkboxes
        case 'list_string':
          $storage = $target->get($field_name);
          $index = array_search(['value' => $parts['value']], $storage->getValue());
          if ($index !== FALSE && !$value) $storage->removeItem($index);
          if ($index === FALSE && $value) $storage->appendItem($parts['value']);
        break;

        // Single Checkbox
        case 'boolean':
        case 'string':
        case 'integer':
          $target->set($field_name, $value);
        break;

        // Other
        default:
          if ($value == '_none') $value = NULL;
          $target->set($field_name, $value);
        break;
      }

      $target->save();

      return new \Symfony\Component\HttpFoundation\JsonResponse([], 200);
    }
  }
}
