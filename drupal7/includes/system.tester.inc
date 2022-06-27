<?php

/**
 * @file
 * Base system URL tests.
 */

/**
 * Implements hook_tester_crawl().
 */
function system_tester_crawl($limit = 0) {
  $items['home'] = array(
    'prefix' => 'Testing home page',
    'paths' => array(url('<front>')),
  );
  $items['404'] = array(
    'prefix' => 'Testing 404 page',
    'paths' => array('/1h237123gdjsadkjhasdb12e'),
  );
  return $items;
}
