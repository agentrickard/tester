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
function drush_tester_crawl($base_path) {
  // @TODO: Possible setup hook.
  $status = module_exists('dblog');
  if (!$status) {
    module_enable(array('dblog'));
  }
  drush_print('Clearing caches...');
  cache_clear_all();
  drush_print('Starting test crawl of ' . $base_path);
  $items = module_invoke_all('tester_info');

  foreach ($items as $name => $item) {
    // Ensure we need to run the tests based on dependencies.
    $skip = FALSE;
    if (!empty($item['dependencies'])) {
      foreach ($item['dependencies'] as $module) {
        if (!module_exists($module)) {
          $skip = TRUE;
          continue;
        }
      }
    }
    if ($skip) {
      continue;
    }
    $function = $name . '_tester_crawl';
    module_load_include('inc', $item['module'], 'includes/' . $name . '.tester');
    drush_print($item['description']);
    $tests = $function();
    foreach ($tests as $test) {
      if (!empty($test['prefix'])) {
        drush_print($test['prefix']);
      }
      foreach ($test['paths'] as $path) {
        $url = $base_path . $path;
        drush_print('Testing ' . $url);
        // @TODO: perhaps just use CURL.
        drupal_http_request($url);
      }
      if (!empty($test['suffix'])) {
        drush_print($test['suffix']);
      }
    }
  }

  // @TODO: Possible teardown hook.
  if (!$status) {
    module_disable(array('dblog'));
  }
}
