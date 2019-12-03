<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_crew\CrewController.
 */

namespace Drupal\afrikaburn_crew\Controller;


use Drupal\Core\Controller\ControllerBase;


class CrewController extends ControllerBase {

  /**
   * Update a crew application field.
   */
  public static function update($wid = '', $sid = FALSE){
    if ($wid && $sid){

      $field = \Drupal::request()->get('field');
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($wid);
      $elements = $webform->getSubmissionForm()['elements'];
      $target = \Drupal\webform\Entity\WebformSubmission::load($sid);
      $value = \Drupal::request()->get('value');

      preg_match('/(?<name>[^\[]+)\[(?<value>[^\[]+)]/', $field, $parts);

      $field_name = count($parts)
        ? $parts['name']
        : $field;

      $definition = @array_shift(
        array_filter(
          [
            $elements[$field_name]['#type'],
            $elements['admin'][$field_name]['#type']
          ]
        )
      );
      $current = $target->getData()[$field_name];

      switch ($definition){

        // Checkboxes
        case 'checkboxes':
          $current = $current ? $current : [];
          $target->setElementData(
            $field_name,
            $value
              ? array_merge($current, [$parts['value']])
              : array_diff($current, [$parts['value']])
          );
        break;

        // Everything else
        default:
          $target->setElementData(
            $field_name,
            $value
          );
      }

      $target->save();

      return new \Symfony\Component\HttpFoundation\JsonResponse([], 200);
    }

    return FALSE;
  }

}
