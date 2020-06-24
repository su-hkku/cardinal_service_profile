<?php

/**
 * @file
 * cardinal_service_profile.inc
 */

use Drupal\node\NodeInterface;
use Drupal\views\ViewExecutable;

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
 * Implements hook_preprocess_hook().
 */
function cardinal_service_profile_preprocess_flag(&$variables) {
  if (\Drupal::currentUser()->isAuthenticated()) {
    return;
  }

  unset($variables['attributes']['class']);
  $current_url = \Drupal::request()->getRequestUri();
  $separator = strpos($current_url, '?') !== FALSE ? '&' : '?';
  $current_url .= $separator . 'flag_type=' . $variables['flaggable']->getEntityTypeId() . '&flag_id=' . $variables['flaggable']->id() . '&flag=' . $variables['flag']->id();
  $current_url = urlencode($current_url);
  $variables['attributes']['href'] = "/saml_login?destination=$current_url";
}

/**
 * Implements hook_user_login().
 */
function cardinal_service_profile_user_login($account) {
  $request_queries = \Drupal::request()->query;
  if (!$request_queries->has('destination')) {
    return;
  }

  $url = parse_url($request_queries->get('destination'));
  if (empty($url['query'])) {
    return;
  }

  parse_str($url['query'], $params);
  if (!(isset($params['flag_type']) && isset($params['flag_id']) && isset($params['flag']))) {
    return;
  }

  $flag = [
    'flag_id' => $params['flag'],
    'entity_type' => $params['flag_type'],
    'entity_id' => $params['flag_id'],
    'uid' => $account->id(),
  ];

  $flagging_storage = \Drupal::entityTypeManager()->getStorage('flagging');
  if (empty($flagging_storage->loadByProperties($flag))) {
    $flagging_storage->create($flag)->save();
  }
}

/**
 * Implements hook_views_pre_build().
 */
function cardinal_service_profile_views_pre_build(ViewExecutable $view) {
  // On the user dashboard, add a contextual argument to the news view to
  // display the announcement news items.
  if (
    $view->id() == 'stanford_news' &&
    \Drupal::routeMatch()->getRouteName() == 'entity.user.canonical'
  ) {
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => 'Announcements']);
    $view->setArguments(array_keys($terms));
    $view->empty = [];
  }
}
