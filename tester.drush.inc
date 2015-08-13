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
  drush_print('Starting test crawl of ' . $path);
  $data = drupal_http_request($path);
  $nodes = db_query("SELECT nid FROM {node} WHERE status > 0")->fetchAll();
  foreach ($nodes as $node) {
    drush_print($path . url("node/$node->nid"));
    $data = drupal_http_request($path . url("node/$node->nid"));
  }
}
