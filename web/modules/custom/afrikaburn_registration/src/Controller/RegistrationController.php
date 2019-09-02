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
  public static function reuse($nid = FALSE, $cid = FALSE) {
    if ($nid){

      $source = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $destination = $source->createDuplicate();
      $destination->setPublished(FALSE);
      $destination->set('created', time());
      $destination->set('changed', time());
      $destination->set('field_prjr_complete', []);

      foreach(
        [
          'field_form_mode',
          'field_prj_adm_review',
          'field_waps',
          'field_waps_allowed',
          'field_prj_actualising',
          'field_prj_adm_wrangler',
          'field_year_cycle',
          'field_grt_awarded',
          'field_mv_amount_worthy',
          'field_grt_other',
          'field_prj_adm_registration',
          'field_final_placement',
          'field_ice_card',
          'field_placement_card',
          'field_placement_letter_sent',
          'field_prj_adm_latitude',
          'field_prj_adm_longitude',
          'field_plot',
        ] as $field
      ) {
        $destination->set($field, NULL);
      }

      if ($cid) {
        $destination->set('field_collective', [$cid]);
        foreach(
          [
            'field_prj_gen_contact',
            'field_prj_gen_lead',
            'field_prj_gen_fundraiser',
            'field_prj_gen_manager',
            'field_prj_stg_contact',
            'field_prj_lgh_lighter',
            'field_prj_snd_person',
            'field_prj_stc_structural',
            'field_prj_lnt_representative',
            'field_ranger_representative',
            'field_mutant_drivers',
            ] as $field
        )
        $destination->set($field, NULL);
      }

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
