<?php

/**
 * @file
 * cardinal_service_profile.inc
 */

use Drupal\node\NodeInterface;

/**
 * Implements hook_ENTITY_TYPE_presave().
 */
function cardinal_service_profile_node_presave(NodeInterface $node) {
  if ($node->bundle() != 'su_opportunity' || !$node->hasField('su_opp_application_deadline')) {
    return;
  }

  $deadline_date = $node->get('su_opp_application_deadline')->getString();
  if (empty($deadline_date)) {
    return;
  }

  list(, $month, $day) = explode('-', substr($deadline_date, 0, strpos($deadline_date, 'T')));

  // Compare the month and day of the deadline field value to taxonomy terms to
  // find out what term has the applicable date range.
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => 'su_opportunity_deadline']);
  /** @var \Drupal\taxonomy\TermInterface $term */
  foreach ($terms as $tid => $term) {
    $deadline_range = $term->get('su_opp_deadline_date')->getValue();
    // The term doesn't have the field value set, skip it.
    if (empty($deadline_range[0]['value'])) {
      continue;
    }

    list(, $from_month, $from_day) = explode('-', $deadline_range[0]['value']);
    list(, $to_month, $to_day) = explode('-', $deadline_range[0]['end_value']);

    // This term is configured for the range surrounding the deadline date field
    // so we use that term id as the deadline term.
    if ($month >= $from_month && $month <= $to_month && $day >= $from_day && $day <= $to_day) {
      $node->set('su_opp_deadline_time', $tid);
      return;
    }

  }

}

/**
 * Implements hook_ENTITY_TYPE_insert().
 */
function cardinal_service_profile_flagging_insert($entity) {
  $session = \Drupal::request()->getSession();
  if (\Drupal::currentUser()->isAnonymous()) {
    // Set a setting that can be retrieved when the user logs in.
    $session->set('flagging_anonymous_session_id', $session->getId());
  }
}

/**
 * Implements hook_user_login().
 */
function cardinal_service_profile_user_login($account) {
  $session = \Drupal::request()->getSession();
  $anonymous_session_id = $session->get('flagging_anonymous_session_id');
  // The user didn't have anything flagged, we can bail.
  if (empty($anonymous_session_id)) {
    return;
  }

  $flag_storage = \Drupal::entityTypeManager()->getStorage('flagging');
  /** @var \Drupal\flag\FlaggingInterface[] $favorites */
  $favorites = $flag_storage->loadByProperties(['session_id' => $anonymous_session_id]);


  // Loop through the favorites from the session when the user was anonymous
  // and either migrate them to the user or delete them.
  foreach ($favorites as $favorite) {

    $entity_type = $favorite->getFlaggableType();
    $entity_id = $favorite->getFlaggableId();

    // The user has already flagged this favorite item, we can delete the flag
    // from the anonymous user.
    if ($flag_storage->loadByProperties([
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'uid' => $account->id(),
    ])) {
      $favorite->delete();
      continue;
    }

    // Move the favorite to target the newly logged in user.
    $favorite->setOwner($account);
    $favorite->set('session_id', NULL);
    $favorite->save();
  }
}
