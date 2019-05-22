<?php
/**
 * @file: project-draft.inc
 */


 // /**
//  * Save draft validator.
//  */
// function afrikaburn_registration_validate_draft(&$form, $form_state){

//   $fields = array_keys(array_filter($form_state->getValues()));

//   if ($form_state->input['title']){
//     $fields['type'] = $form_state->getFormObject()->getEntity()->type->entity->id();
//     $node = Node::create($fields);

//     $node->setPublished(FALSE);
//     $node->save();
//   }


//   // foreach($fields as $name=>$value){
//   //   try{

//   //     $value = $form_state->getValue($name);
//   //     $base_value = is_array($value)
//   //       ? array_filter(
//   //           [
//   //             array_column($value, 'target_id'),
//   //             array_column($value, 'value'),
//   //           ]
//   //         )[0][0]
//   //       : $value;

//   //     $node->set($name, $base_value);
//   //   } catch (Exception $e) {
//   //     // Ignoring all meta fields, actions, etc.
//   //   }
//   // }

// }

// /**
//  * Save draft submit handler.
//  */
// function afrikaburn_registration_save_draft(&$form, $form_state){

//   drupal_set_message('Draft registration saved.', true);

//   $mode = $form_state->get('field_form_mode')->value;
//   $form_state->setRedirectUrl(\Drupal::url('node/edit/' . $node->id() . '/' . $mode));
// }
