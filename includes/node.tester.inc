<?php

/**
 * @file
 * Base tests for node paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function node_tester_crawl($limit = 0) {
  $paths = array();
  if (!$limit) {
    $nodes = db_query("SELECT nid FROM {node} WHERE status > 0")->fetchAll();
  }
  else {
    $list = db_query("SELECT nid, type FROM {node} WHERE status > 0")->fetchAll();
    foreach ($list as $record) {
      $types[$record->type][] = $record;
    }
    $count = count(node_type_get_types());
    $slice = ceil($limit / $count);
    $nodes = array();
    foreach ($types as $type) {
      $nodes = array_merge($nodes, array_slice($type, 0, $slice));
    }
    $nodes = array_slice($nodes, 0, $limit);
  }
  foreach ($nodes as $node) {
    $paths[] = url("node/$node->nid");
  }
  $items['node'] = array(
    'paths' => $paths,
  );
  return $items;
}
