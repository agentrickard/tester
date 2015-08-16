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
 * {type}.inc.
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
 *  An array of _internal_ Drupal paths, created by url() is preferred.
