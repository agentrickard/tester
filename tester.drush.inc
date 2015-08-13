<?php

/**
 * @file
 * Tester module drush commands.
 */

/**
 * Implements hook_drush_command().
 */
function tester_drush_command() {

  $items['tester-crawl'] = array(
    'description' => 'Crawl a site looking for notices.',
    'examples' => array('tester-crawl' => 'Crawl the site'),
    'aliases' => array('tc'),
    'arguments' => array(
      'base_url' => 'Base url of the Drupal instanace. e.g. http://example.com/',
    ),
  );

  return $items;
}

/**
 * Crawl a site.
 */
function drush_tester_crawl($path) {
  $status = module_exists('watchdog');
  if (!$status) {
    module_enable(array('watchdog'));
  }
  drush_print('Starting test crawl of ' . $path);
  $items = module_invoke_all('tester_info');

  foreach ($items as $name => $item) {
    $function = $name . '_tester_crawl';
    drush_print($item['description']);
    $tests = $function($path);
    drush_print_r($tests);
  }

  if (!$status) {
    module_disable(array('watchdog'));
  }
}
