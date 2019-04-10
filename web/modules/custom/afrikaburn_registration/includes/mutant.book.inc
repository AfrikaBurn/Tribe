<?php
/**
 * @file: mutant.book.inc
 * Contains Mutant Vehicle book printing utilities.
 */



function _mutant_titles(&$build){

  $build['field_prj_saf_passengers']['#title'] = 'Passengers';
  $build['field_prj_trl_passengers']['#title'] = 'On trailer';

  $build['author']['#title'] = 'Applicant';
  $build['field_prj_gen_owner']['#title'] = 'Owner';
  $build['field_prj_gen_lead']['#title'] = 'Artist';
  $build['field_prj_gen_contact']['#title'] = 'Contact';
  $build['field_prj_oth_associated']['#title'] = 'Associated';

  $build['field_prj_trl_describe']['#title'] = 'Description';
  $build['field_prj_trl_capasity']['#title'] = 'Getting on and off'; // IDK - WTF

  $build['field_prj_brn_plan']['#title'] = 'Decription';
  $build['field_prj_brn_desc']['#title'] = 'Safety';

  $build['field_prj_lgh_description']['#title'] = 'Vehicle';
  $build['field_prj_lgh_projection']['#title'] = 'Trailer and A-frame';

  $build['field_prj_snd_description']['#title'] = 'Description';
  $build['field_prj_snd_safety']['#title'] = 'Safety';
}


function _mutant_support_camp(&$build, $entity, $display){
  if ($display->getComponent('support_camp')) {

    $supporting = \Drupal::entityTypeManager()->getStorage('node')->load(
      array_shift(
        \Drupal::entityQuery('node')
        ->condition('type', 'theme_camps')
        ->condition('field_supporting_project', $entity->id())
        ->execute()
      )
    );

    if ($supporting) {
      $build['support_camp'] = [
        '#prefix' => '<div class="field">',
        '#markup' =>
          '<div class="field__label">Support Camp</div>' .
          '<div class="field__item">' .
            $supporting->get('title')->value.
          '</div>',
        '#suffix' => '</div>',
      ];
    }
  }
}


function _mutant_registrant(&$build, $entity, $display){
  if ($display->getComponent('registrant')) {

    $registrant = \Drupal::entityTypeManager()->getStorage('user')->load($entity->uid->getValue()[0]['target_id']);

    $build['registrant'] = [
      '#prefix' => '<div class="field">',
      '#markup' =>
        '<div class="field__label">Registrant</div>' .
        '<div class="field__item">' .
          render(
            \Drupal::entityTypeManager()->getViewBuilder('user')->view($registrant, 'full_name')
          ) .
        '</div>',
      '#suffix' => '</div>',
    ];
  }
}


function _mutant_drivers(&$build, $entity, $display){
  if ($display->getComponent('drivers')) {

    $build['drivers'] = views_embed_view('wrangle_mutant_drivers', 'default', $entity->id());

  }
}