<?php

/**
 * @file
 * Base tests for menu paths.
 */

/**
 * Implements hook_tester_crawl().
 */
function menu_tester_crawl($limit = 0) {
  $paths = array();
  if (!$limit) {
    $links = db_query("SELECT link_path FROM {menu_links} WHERE hidden < 1 AND menu_name NOT IN ('devel', 'management')")->fetchAll();
  }
  else {
    $list = db_query("SELECT link_path, menu_name FROM {menu_links} WHERE hidden < 1 AND menu_name NOT IN ('devel', 'management')")->fetchAll();
    foreach ($list as $record) {
      $menus[$record->menu_name][] = $record;
    }
    $count = count(menu_get_menus()) - 2;
    if (!$count) {
      $count = 1;
    }
    $slice = ceil($limit / $count);
    $links = array();
    foreach ($menus as $menu) {
      $links = array_merge($links, array_slice($menu, 0, $slice));
    }
    $links = array_slice($links, 0, $limit);
  }
  // @TODO: move into query?
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
