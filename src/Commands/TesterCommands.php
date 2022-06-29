<?php

namespace Drupal\tester\Commands;

use Drupal\tester\TesterPluginManager;
use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

/**
 * Defines the class for our drush commands.
 */
class TesterCommands extends DrushCommands {

  /**
   * @var \Drupal\tester\TesterPluginManager
   */
  protected $pluginManager;

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
   * @param \Drupal\tester\TesterPluginManager $plugin_manager
   *   The tester plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The default http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state interface
   */
  public function __construct(TesterPluginManager $plugin_manager, ConfigFactoryInterface $config_factory, Client $http_client, StateInterface $state) {
    $this->pluginManager = $plugin_manager;
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
    echo "Crawling URLs\n";

    if (is_null($base_url)) {
      GLOBAL $base_url;
    }

    $urls = $this->getUrls();

    // We want to test 403 and 404 pages, so allow them.
    // See https://docs.guzzlephp.org/en/stable/request-options.html#http-errors
    // We also do some status reporting.
    // See https://docs.guzzlephp.org/en/stable/request-options.html#on-stats
    $options = [
      'http_errors' => FALSE,
      'on_stats' => function (TransferStats $stats) {
        echo "  - Status: " . $stats->getResponse()->getStatusCode() . "\n";
      },
    ];

    foreach ($urls as $url) {
      $path = $base_url . $url;
      echo " • $path\n";
      $this->httpClient->request('GET', $path, $options);
    }
  }

  /**
   * Retrieves the list of URLs to test.
   *
   * @return array
   *   An array of URLs.
   */
  private function getUrls() {
    $urls = [];

    $plugins = $this->pluginManager->getDefinitions();

    foreach (array_keys($plugins) as $id) {
      // @todo Check dependencies.
      $instance = $this->pluginManager->createInstance($id);
      $urls = array_merge($urls, $instance->urls());
    }

    return $urls;
  }

}
