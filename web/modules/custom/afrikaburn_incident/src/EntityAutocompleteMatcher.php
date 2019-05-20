<?php
namespace Drupal\afrikaburn_incident;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
class EntityAutocompleteMatcher extends \Drupal\Core\Entity\EntityAutocompleteMatcher {
  /**
   * Gets matched labels based on a given search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {
    $matches = [];
    $options = [
      'target_type'      => $target_type,
      'handler'          => $selection_handler,
      'handler_settings' => $selection_settings,
    ];
    $handler = $this->selectionManager->getInstance($options);
    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 10);
      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          $source_entity = \Drupal::entityTypeManager()->getStorage($target_type)->load($entity_id);
          $entity = \Drupal::entityManager()->getTranslationFromContext($source_entity);
          $type = !empty($entity->type->entity) ? $entity->type->entity->label() : $entity->bundle();

          switch($type) {
            case 'user': $label = $this->getUserLabel($entity); break;
            case 'Incident': $label = $this->getIncidentLabel($entity); break;
            default: $label .= ' [' . $type . ']';
          }

          $matches[] = [
            'value' => $this->getKey($label, $entity_id),
            'label' => $label
          ];
        }
      }
    }
    return $matches;
  }

  private function getKey($label, $entity_id){
    $key = $label . ' (' . $entity_id . ')';
    $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
    return Tags::encode($key);
  }

  private function getUserLabel($entity){
    return
      $entity->field_first_name->value .
      ' "' . $entity->name->value . '" ' .
      $entity->field_last_name->value;
  }

  private function getIncidentLabel($entity){
    return
      '#' . $entity->nid->value .
      ' | ' . $entity->field_date_and_time->date->format('D d/m/Y G:i') .
      ' | ' . $entity->title->value;
  }
}