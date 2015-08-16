<?php

/**
 * @file
 * Base tests for user paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function user_tester_crawl($path) {
  $paths = array();
  $users = db_query("SELECT uid FROM {users} WHERE status > 0")->fetchAll();
  foreach ($users as $user) {
    $paths[] = $path . url("user/$user->uid");
  }
  $items['user'] = array(
    'message' => 'Testing user paths.',
    'paths' => $paths,
  );
  return $items;
}
