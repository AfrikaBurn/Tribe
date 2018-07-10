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
   * Copy an archived registration
   */
  public static function reuse($nid = FALSE) {

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

