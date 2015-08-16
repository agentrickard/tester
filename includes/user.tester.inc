<?php

/**
 * @file
 * Base tests for user paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function user_tester_crawl() {
  $paths = array();
  $users = db_query("SELECT uid FROM {users} WHERE status > 0")->fetchAll();
  foreach ($users as $user) {
    $paths[] = url("user/$user->uid");
  }
  $items['user'] = array(
    'paths' => $paths,
  );
  return $items;
}
