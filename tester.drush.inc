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
  $line = '-----------------';
  $bars = '=================';
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
    drush_print($bars);
    drush_print($item['description']);
    drush_print($bars);
    $tests = $function();
    foreach ($tests as $test) {
      if (!empty($test['prefix'])) {
        drush_print($line);
        drush_print($test['prefix']);
        drush_print($line);
      }
      foreach ($test['paths'] as $path) {
        $url = $base_path . $path;
        drush_print('Testing ' . $url);
        // @TODO: perhaps just use CURL.
        drupal_http_request($url);
      }
      if (!empty($test['suffix'])) {
        drush_print($line);
        drush_print($test['suffix']);
        drush_print($line);
      }
    }
  }

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
  $bars = '==================================================';

  // All errors.

  $errors = db_query("SELECT location, severity, message, variables FROM {watchdog} WHERE type = 'php'")->fetchAll();

  drush_print($bars);
  drush_print('Complete error report: ' . count($errors) . ' errors');
  drush_print($bars);
  $rows = array();
  $rows[] = array('Location', 'Message');

  foreach ($errors as $error) {
    $rows[] = array($error->location, strip_tags(t($error->message, unserialize($error->variables))));
  }
  drush_print_table($rows, TRUE);

  // Unique errors.

  $errors_unique = db_query("SELECT message, location, severity, variables FROM {watchdog} WHERE type = 'php' GROUP BY message")->fetchAll();

  drush_print($bars);
  drush_print('Unique error report: ' . count($errors_unique) . ' unique errors');
  drush_print($bars);
  $rows = array();
  $rows[] = array('Location', 'Message');

  foreach ($errors_unique as $error) {
    $rows[] = array($error->location, strip_tags(t($error->message, unserialize($error->variables))));
  }

  drush_print_table($rows, TRUE);

  // Summary errors.

  drush_print($bars);
  drush_print('Summary error report');
  drush_print($bars);

  drush_print('Total errors: ' . count($errors));
  drush_print('Unique errors: ' . count($errors_unique));

}
