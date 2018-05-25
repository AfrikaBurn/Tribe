<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_emails\AfrikaBurnEmailPermissions.
 */

namespace Drupal\afrikaburn_emails;

class Permissions {
  /**
   * Get permissions for AfrikaBurn Emails.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {

    $config = \Drupal::config('afrikaburn_emails.settings');
    $message_definition = $config->get('message_definition');
    $definition_pairs = explode("\n", $message_definition);

    $permissions = [];
    if (is_array($definition_pairs)){
      foreach($definition_pairs as $key_label){
        list($key, $label, $recipient) = explode('|', $key_label);
        $permissions['edit ' . str_replace(':', ' ', $key) . ' template'] = [
          'title' => 'Edit the ' . $label . ' email template',
        ];
      }
    }

    return $permissions;
  }
}
