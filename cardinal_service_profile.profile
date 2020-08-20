<?php

/**
 * @file
 * cardinal_service_profile.profile
 */

require_once 'cardinal_service_profile.inc';

/**
 * Implements hook_install_tasks().
 */
function cardinal_service_profile_install_tasks(&$install_state) {
  return ['cardinal_service_profile_final_task' => []];
}

/**
 * Perform final tasks after the profile has completed installing.
 *
 * @param array $install_state
 *   Current install state.
 */
function cardinal_service_profile_final_task(array &$install_state) {
  \Drupal::service('plugin.manager.install_tasks')->runTasks($install_state);
}

function miketest(){
  $cache = \Drupal::cache()->get('mikestest');

  $dom = new DOMDocument();
  $dom->loadXML($cache->data);
  $xpath = new DOMXPath($dom);

  /** @var \DOMElement $opp */
  foreach($xpath->query('//OPPORTUNITY') as $opp){
    $open = $xpath->query('SUBMISSIONS_OPEN', $opp)->item(0)->nodeValue;
    $closed = $xpath->query('APPLICATION_DEADLINE', $opp)->item(0)->nodeValue;
    if($open == $closed || !$closed){
      dpm($xpath->query('ID', $opp)->item(0)->nodeValue);
    }
//    return;
  }
}
