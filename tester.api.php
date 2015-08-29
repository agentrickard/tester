<?php

/**
 * @file
 * API documentation file for Tester module.
 */

/**
 * Registration hook: hook_tester_info().
 *
 * Tester is designed to quickly spider your public-facing pages. It uses a
 * simple API and magic file naming to register include files that define
 * a set of URLs to crawl.
 *
 * Files are expected to be in the directory {module}/includes/ and to be named
 * {type}.tester.inc.
 *
 * The values for {module} and {type} are read from the 'module' and key values
 * returned by this hook.
 *
 * @return array
 *   'description' => Required. Message to display when executing the test.
 *   'module' => Required. Module folder that contains the test file.
 *   'dependencies' => Optional. An array of modules that must be present for
 *    test to fire. If these are not enabled, the test will be skipped.
 */
function hook_tester_info() {
  // Registers a test file at PATH_TO_MODULE/tester/includes/system.tester.inc
  $items['system'] = array(
    'description' => 'Testing base system URLs.',
    'module' => 'tester',
    'dependencies' => array(),
  );
  return $items;
}

/**
 * Implementation hook: hook_tester_crawl().
 *
 * Returns an array of URLs to crawl, looking for PHP errors.
 *
 * @return array
 *   'prefix' => Optional. A message to print before crawling the URLs.
 *   'paths' => Required. An array of _internal_ Drupal paths to crawl.
 *     Creation of these paths by using the url() function is preferred.
 *   'suffix' => Optional. A message to print after crawling the URLs.
 */
function hook_tester_crawl() {
  // Hit the homepage.
  $items['home'] = array(
    'prefix' => 'Testing home page',
    'paths' => array(url('<front>')),
  );
  // Hit a non-existent page.
  $items['404'] = array(
    'prefix' => 'Testing 404 page',
    'paths' => array('/1h237123gdjsadkjhasdb12e'),
  );
  return $items;
}
