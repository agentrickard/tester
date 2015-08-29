<?php

/**
 * @file
 * Tester module drush commands.
 */

define(TESTER_BARS,  '==================================================');
define(TESTER_LINE, '--------------------------------------------------');

/**
 * Implements hook_drush_command().
 */
function tester_drush_command() {

  $items['tester-crawl'] = array(
    'description' => 'Crawl a site looking for notices.',
    'examples' => array(
      'tester-crawl http://example.com' => 'Crawl the site example.com',
      'tester-crawl http://example.com menu' => 'Crawl the menu tests for the site',
      'tc http://example.com menu' => 'Crawl the menu tests for the site',
    ),
    'aliases' => array('tc'),
    'arguments' => array(
      'base_url' => 'Base url of the Drupal instanace. e.g. http://example.com/',
      'type'=> 'The test batch to run. (e.g. "all", "node", or "menu".)',
    ),
  );

  return $items;
}

/**
 * Crawl a site.
 */
function drush_tester_crawl($base_path, $type = NULL) {
  // Allow user choice of the test run.
  $items = tester_tester_info();
  $items += module_invoke_all('tester_info');
  if (!isset($type) || ($type != 'all' && !isset($items[$type]))) {
    $list = array(1 => 'All');
    foreach ($items as $key => $item) {
      $skip = FALSE;
      if (!empty($item['dependencies'])) {
        foreach ($item['dependencies'] as $module) {
          if (!module_exists($module)) {
            $skip = TRUE;
            continue;
          }
        }
      }
      if (!$skip) {
        $list[count($list)+1] = $key;
      }
    }
    $type = drush_choice($list, 'Enter a number to choose which crawl to perform.', '!value');

    if (empty($type)) {
      return drush_user_abort();
    }
  }
  if (isset($items[$type])) {
    $items = array($type => $items[$type]);
  }
  else if ($type > 1) {
    $items = array_slice($items, $type - 2, 1, TRUE);
  }

  // @TODO: Possible setup hook.
  $status = module_exists('dblog');
  if (!$status) {
    module_enable(array('dblog'));
  }
  drush_print('Clearing caches...');
  cache_clear_all();
  drush_print('Truncating {watchdog}');
  db_query('TRUNCATE {watchdog}');
  drush_print('Starting test crawl of ' . $base_path);

  // Set watchdog variables.
  global $conf;
  $temp['dblog_row_limit'] = $conf['dblog_row_limit'];
  $conf['dblog_row_limit'] = 0;
  $temp['error_level'] = $conf['error_level'];
  $conf['error_level'] = ERROR_REPORTING_DISPLAY_ALL;

  $count = 0;
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
    // Load functions.
    $function = $name . '_tester_crawl';
    if (!function_exists($function)) {
      include_once 'includes/' . $name . '.tester.inc';
    }
    else if (!function_exists($function)) {
      module_load_include('inc', $item['module'], 'includes/' . $name . '.tester');
    }

    drush_print(TESTER_LINE);
    drush_print($item['description']);
    drush_print(TESTER_LINE);
    $tests = $function();
    foreach ($tests as $test) {
      if (!empty($test['prefix'])) {
        drush_print(TESTER_LINE);
        drush_print($test['prefix']);
        drush_print(TESTER_LINE);
      }
      foreach ($test['paths'] as $path) {
        $url = $base_path . $path;
        drush_print('Testing ' . $url);
        // @TODO: perhaps just use CURL.
        drupal_http_request($url);
      }
      if (!empty($test['suffix'])) {
        drush_print(TESTER_LINE);
        drush_print($test['suffix']);
        drush_print(TESTER_LINE);
      }
    }
    $count += count($tests);
  }

  drush_print(TESTER_LINE);
  drush_print($count . " pages crawled");
  tester_dblog_report();

  // Reset vars.
  $conf['dblog_row_limit'] = $temp['dblog_row_limit'];
  $conf['error_level'] = $temp['error_level'];
  db_query('TRUNCATE {watchdog}');

  // @TODO: Possible teardown hook.
  if (!$status) {
    module_disable(array('dblog'));
  }
}

/**
 * Generate the dblog report.
 */
function tester_dblog_report() {
  // All errors.

  $errors = db_query("SELECT location, severity, message, variables FROM {watchdog} WHERE type = 'php'")->fetchAll();

  if (empty($errors)) {
    drush_print(TESTER_LINE);
    drush_print('No errors detected during test crawl.');
    drush_print(TESTER_LINE);
    return;
  }

  $list = array(1 => 'Full Report', 2 => 'Unique Errors', 3 => 'Summary Report');
  $type = drush_choice($list, count($errors) . ' errors detected. Choose report type:', '!value');

  if (empty($type)) {
    return drush_user_abort();
  }

  else if ($type == 1) {
    drush_print(TESTER_LINE);
    drush_print('Complete error report: ' . count($errors) . ' errors');
    drush_print(TESTER_LINE);
    $rows = array();
    $rows[] = array('Location', 'Message');

    foreach ($errors as $error) {
      $rows[] = array($error->location, strip_tags(t($error->message, unserialize($error->variables))));
    }
    drush_print_table($rows, TRUE);
  }
  // Unique errors.
  else if ($type == 2) {
    $errors_unique = db_query("SELECT message, location, severity, variables FROM {watchdog} WHERE type = 'php' GROUP BY message")->fetchAll();

    drush_print(TESTER_LINE);
    drush_print('Unique error report: ' . count($errors_unique) . ' unique errors');
    drush_print(TESTER_LINE);
    $rows = array();
    $rows[] = array('Location', 'Message');

    foreach ($errors_unique as $error) {
      $rows[] = array($error->location, strip_tags(t($error->message, unserialize($error->variables))));
    }
    drush_print_table($rows, TRUE);
  }
  // Summary errors.
  else {
    drush_print(TESTER_LINE);
    drush_print('Summary error report');
    drush_print(TESTER_LINE);

    drush_print('Total errors: ' . count($errors));
    drush_print('Unique errors: ' . count($errors_unique));
  }
}

/**
 * Implements hook_tester_info() {
 */
function tester_tester_info() {
  $items['system'] = array(
    'description' => 'Testing base system URLs.',
    'module' => 'tester',
    'dependencies' => array(),
  );
  $items['menu'] = array(
    'description' => 'Testing menu items.',
    'module' => 'tester',
    'dependencies' => array('menu'),
  );
  $items['node'] = array(
    'description' => 'Testing node pages.',
    'module' => 'tester',
    'dependencies' => array('node'),
  );
  $items['user'] = array(
    'description' => 'Testing user pages.',
    'module' => 'tester',
    'dependencies' => array('user'),
  );
  return $items;
}
