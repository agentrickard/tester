<?php

namespace Drupal\tester\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\Client;

/**
 * Defines the class for our drush commands.
 */
class TesterCommands extends DrushCommands {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs the class.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The default http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state interface
   */
  public function __construct(ConfigFactoryInterface $config_factory, Client $http_client, StateInterface $state) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->state = $state;
  }

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
   * @param string $base_url
   *   The base URL to use when crawling the site. No trailing slash.
   *   If not provided, the global $base_url value will be used.
   *
   * @command tester:crawl
   * @aliases tester-crawl, tc
   * @usage drush tester:crawl, drush tc
   */
  public function crawl($base_url = NULL) {
    if (is_null($base_url)) {
      GLOBAL $base_url;
    }
    $urls = $this->getUrls();
    echo "Crawling URLs\n";
    foreach ($urls as $url) {
      echo " • $base_url$url\n";
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
      '/admin',
      '/foo-bar',
    ];
  }

}
