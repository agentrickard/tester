<?php

namespace Drupal\tester\Commands;

use Drupal\tester\TesterPluginManager;
use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The default http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state interface
   */
  public function __construct(TesterPluginManager $plugin_manager, ModuleHandlerInterface $module_handler, Client $http_client, ConfigFactoryInterface $config_factory, StateInterface $state) {
    $this->pluginManager = $plugin_manager;
    $this->moduleHandler = $module_handler;
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
    // We also do some simple status reporting.
    // See https://docs.guzzlephp.org/en/stable/request-options.html#on-stats
    $options = [
      'http_errors' => FALSE,
      'on_stats' => function (TransferStats $stats) {
        echo "  - Status: " . $stats->getResponse()->getStatusCode() . "\n";
      },
    ];

    if (empty($urls)) {
      echo "No valid plugins were found. \n";
    }
    else {
      foreach ($urls as $url) {
        $path = $base_url . $url;
        echo " • $path\n";
        $this->httpClient->request('GET', $path, $options);
      }
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
      $instance = $this->pluginManager->createInstance($id);
      $dependencies = $instance->dependencies();
      if ($this->isAllowed($dependencies)) {
        $urls = array_merge($urls, $instance->urls());
      }
    }

    return $urls;
  }

  /**
   * Determines if a plugin is valid, based on dependencies.
   *
   * @param array $dependencies
   *   The dependencies, as defined in TesterPluginInterface.
   *
   * @return bool
   *   TRUE if the plugin is valid.
   */
  private function isAllowed(array $dependencies) {
    $return = TRUE;
    // @todo Right now we only handle modules.
    // We would need to inject the theme handler service.
    foreach ($dependencies as $type => $extensions) {
      switch ($type) {
        case "modules":
        default:
          foreach ($extensions as $extension) {}
          if (!$this->moduleHandler->moduleExists($extension)) {
            $return = FALSE;
          }
          break;
      }
    }
    return $return;
  }

}
