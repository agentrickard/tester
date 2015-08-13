<?php

/**
 * @file
 * Base system URL tests.
 */

/**
 * Implements hook_tester_crawl().
 */
function system_tester_crawl($path) {
  $items['home'] = array(
    'message' => 'Testing home page',
    'paths' => array($path),
  );
  $items['404'] = array(
    'message' => 'Testing 404 page',
    'paths' => array($path . '1h237123gdjsadkjhasdb12e'),
  );
  return $items;
}
