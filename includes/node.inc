<?php

/**
 * @file
 * Base tests for node paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function node_tester_crawl($path) {
  $paths = array();
  $nodes = db_query("SELECT nid FROM {node} WHERE status > 0")->fetchAll();
  foreach ($nodes as $node) {
    $paths[] = $path . url("node/$node->nid");
  }
  $items['node'] = array(
    'message' => 'Testing node paths.',
    'paths' => $paths,
  );
  return $items;
}
