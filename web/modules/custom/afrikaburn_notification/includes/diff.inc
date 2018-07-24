<?php

/**
 * Compute an entity diff.
 */
function _diff($entity){

  static $diff;

  if (is_object($entity)){
    if (!$diff[$entity->id()]){
      $diff[$entity->id()] = $entity->original
        ? _entity_compare($entity, $entity->original)
        : FALSE;
    }
    return $diff[$entity->id()];
  }

  return $diff[$entity];
}

// Compute entity diff
function _entity_compare($entity_new, $entity_old) {

  $bundle_fields = \Drupal::entityManager()->getFieldDefinitions('node', $entity_new->bundle());

  foreach($bundle_fields as $name=>$definition){

    if ($name == 'title' || preg_match('/^field_/', $name)){

      switch($definition->get('field_type')){

        case 'entity_reference':
        case 'image':
          $old = array_column($entity_old->get($name)->getValue(), 'target_id');
          $new = array_column($entity_new->get($name)->getValue(), 'target_id');
          if ($new != $old) {
            $diff[$definition->get('label')] = [count($old), count($new)];
          }
        break;

        default:
          $old = $entity_old->get($name)->value;
          $new = $entity_new->get($name)->value;

          if ($new != $old){
            $diff[$definition->get('label')] = [$old, $new];
          }

      }
    }
  }

  return $diff;
}
