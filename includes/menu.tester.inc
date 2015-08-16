<?php

/**
 * @file
 * Base tests for menu paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function menu_tester_crawl() {
  $paths = array();
  $links = db_query("SELECT link_path FROM {menu_links} WHERE hidden < 1")->fetchAll();
  foreach ($links as $link) {
    if (strpos($link->link_path, '%') === FALSE && !url_is_external($link->link_path)) {
      $paths[] = url($link->link_path);
    }
  }
  $items['menu'] = array(
    'paths' => $paths,
  );
  return $items;
}
