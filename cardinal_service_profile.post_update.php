<?php

/**
 * @file
 * cardinal_service_profile.install
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Implements hook_removed_post_updates().
 */
function cardinal_service_profile_removed_post_updates() {
  return [];
}

/**
 * Move field data for spotlights.
 */
function cardinal_service_profile_post_update_spotlight1() {
  FieldStorageConfig::create([
    'uuid' => '5e126799-c3f1-4b09-bb97-006e8ac0aad2',
    'entity_type' => 'node',
    'type' => 'text_with_summary',
    'field_name' => 'su_spotlight_quote',
  ])->save();
  FieldConfig::create([
    'uuid' => '65cfa3f8-3ad4-4462-9e9c-4d13311fd777',
    'field_name' => 'su_spotlight_quote',
    'label' => 'Quote',
    'entity_type' => 'node',
    'bundle' => 'su_spotlight',
    'settings' => ['display_summary' => TRUE],
  ])->save();

  $nodes = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'su_spotlight']);
  /** @var \Drupal\node\NodeInterface $node */
  foreach ($nodes as $node) {
    if ($body = $node->get('body')->getValue()) {
      $quote = [
        'value' => '<p>' . $body[0]['summary'] . '</p>',
        'summary' => strip_tags($body[0]['value']),
        'format' => $body[0]['format'],
      ];
      $student_name = $node->get('su_spotlight_student_name')->getString();
      if ($student_name == $node->label()) {
        $node->set('su_spotlight_student_name', NULL);
      }

      $node->set('su_spotlight_quote', $quote)
        ->set('body', NULL)
        ->save();
    }
  }
}
