<?php

/**
 * Compute an entity diff.
 */
function _diff($entity){
  $entity->original ? _entity_compare($entity, $entity->original) : FALSE;
}

// Compute entity diff
function _entity_compare($entity_new, $entity_old) {

  $bundle_fields = \Drupal::entityManager()->getFieldDefinitions('node', $entity_new->bundle());

  foreach($bundle_fields as $name=>$definition){

    if ($name == 'title' || preg_match('/^field_/', $name)){

      $old = $entity_old->get($name)->value;
      $new = $entity_new->get($name)->value;

      if ($new != $old){
        $diff[$definition->title] = [$old, $new];
      }
    }
  }

  return $diff;
}
