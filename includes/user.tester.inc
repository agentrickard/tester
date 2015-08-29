<?php

/**
 * @file
 * Base tests for user paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function user_tester_crawl($limit = 0) {
  $paths = array();
  if (!$limit) {
    $users = db_query("SELECT uid FROM {users} WHERE status > 0")->fetchAll();
  }
  // @TODO: limit by role.
  else {
    $users = db_query_range("SELECT uid FROM {users} WHERE status > 0", 0, $limit)->fetchAll();
  }
  foreach ($users as $user) {
    $paths[] = url("user/$user->uid");
  }
  $items['user'] = array(
    'paths' => $paths,
  );
  return $items;
}
