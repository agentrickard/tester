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
    $urls = $this->getUrls();
    echo "Crawling URLS\n";
    foreach ($urls as $url) {
      echo "• $url\n";
    }
  }

  /**
   * Retrieves the list of URLs to test.
   *
   * @return array
   *   An array of URLs.
   */
  private function getUrls() {
    // @todo Use the plugin system. https://palantir.atlassian.net/browse/PHP-3
    return [
      '/',
      'admin',
      'foo-bar',
    ];
  }

}
