<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_bio\PeopleController.
 */

namespace Drupal\afrikaburn_bio\Controller;


use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class PeopleController extends ControllerBase {

  /**
   * Update a user field.
   */
  public static function update($uid = FALSE){
    if ($uid){

      $field = \Drupal::request()->get('field');
      $target = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
      $value = \Drupal::request()->get('value');

      preg_match('/(?<name>[^\[]+)\[(?<value>[^\[]+)]/', $field, $parts);

      $field_name = count($parts)
        ? $parts['name']
        : $field;

      $definition = $target->get($field_name)->getFieldDefinition()->getType();

      switch ($definition){

        // Multiple checkboxes
        case 'list_string':
          $storage = $target->get($field_name);
          $index = array_search(['value' => $parts['value']], $storage->getValue());
          if ($index !== FALSE && !$value) $storage->removeItem($index);
          if ($index === FALSE && $value) $storage->appendItem($parts['value']);
        break;

        // Other
        case count($parts) == 0:
          if ($value == '_none') $value = NULL;
          $target->set($field, $value);
        break;

        default:
          $target->set($field_name, $value);
      }

      $target->save();

      return new \Symfony\Component\HttpFoundation\JsonResponse(
        [
          "tr[data-uid=$uid] .views-field-field-quicket-id" => $target->field_quicket_code->value . ' / ' . $target->field_quicket_id->value
        ],
        200
      );
    }
  }

  /**
   * Send magic login link.
   */
  public static function reset($uid = FALSE){
    if ($uid && $uid > 1){
      $target = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
      $target->set('status', 0); $target->save();
      $target->set('status', 1); $target->save();
      drupal_set_message(
        t('Mail with magic login link sent!'),
        'status',
        TRUE
      );
    } else {
      drupal_set_message(
        t('Cannot send login link to that user!'),
        'status',
        TRUE
      );
    }

    return new RedirectResponse(\Drupal\Core\Url::fromUserInput(\Drupal::destination()->get()));
  }
}
