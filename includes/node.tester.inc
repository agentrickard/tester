<?php

/**
 * @file
 * Base tests for node paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function node_tester_crawl() {
  $paths = array();
  $nodes = db_query("SELECT nid FROM {node} WHERE status > 0")->fetchAll();
  foreach ($nodes as $node) {
    $paths[] = url("node/$node->nid");
  }
  $items['node'] = array(
    'paths' => $paths,
  );
  return $items;
}
