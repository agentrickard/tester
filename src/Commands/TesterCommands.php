<?php

namespace Drupal\tester\Commands;

use Drush\Commands\DrushCommands;

/**
 * Defines the class for our drush commands.
 */
class TesterCommands extends DrushCommands {

  /**
   * Test function.
   *
   * @command tester:test
   * @aliases test
   * @usage drush tester:test
   */
  public function test() {
    echo "Hello World\n";
  }

  /**
   * Crawls a site looking for errors.
   *
   * @command tester:crawl
   * @aliases tester-crawl, tc
   * @usage drush tester:crawl, drush tc
   */
  public function crawl() {
    echo "Hello World\n";
  }

}
