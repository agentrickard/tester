<?php

namespace Drupal\tester\Commands;

use Drupal\tester\TesterPluginManager;
use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

/**
 * Defines the class for our drush commands.
 */
class TesterCommands extends DrushCommands {

  /**
   * The tester plugin manager.
   *
   * @var \Drupal\tester\TesterPluginManager
   */
  protected $pluginManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The default http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The state interface.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The error count for the run.
   *
   * @var array
   */
  protected $errorCount = [];

  /**
   * Constructs the class.
   *
   * @param \Drupal\tester\TesterPluginManager $plugin_manager
   *   The tester plugin manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The default http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state interface.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(TesterPluginManager $plugin_manager, ModuleHandlerInterface $module_handler, ModuleInstallerInterface $module_installer, Client $http_client, ConfigFactoryInterface $config_factory, StateInterface $state, Connection $database) {
    $this->pluginManager = $plugin_manager;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->state = $state;
    $this->database = $database;
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
    $this->setUp();
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
        $this->setErrorStorage($path);
        $response = $this->httpClient->request('GET', $path, $options);
        // @todo Make a get/set for errorCount. Rename to errorLog.
        $this->errorCount[$path]['response'] = $response->getStatusCode();
        $this->captureErrors($path);
      }
    }

    $this->tearDown();
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

  /**
   * Captures the errors for a specific path for display.
   *
   * @param string $path
   *   The URL being tested.
   */
  protected function captureErrors($path) {
    $this->errorCount[$path]['final'] = $this->getWatchdogCount();
    if ($this->errorCount[$path]['final'] > $this->errorCount[$path]['initial']) {
      $count = $this->errorCount[$path]['final'] - $this->errorCount[$path]['initial'];
      $this->errorCount[$path]['errors'] = $this->getErrors($count, $this->errorCount[$path]['initial']);
    }
  }

  /**
   * Gets the errors from {watchdog} and returns them.
   *
   * @param int $count
   *   The number of errors to return.
   * @param int $initial
   *   The record number to start with.
   */
  protected function getErrors(int $count, int $initial) {
    echo $count . "\n";
    echo $initial . "\n";
    $query = $this->database->select('watchdog', 'w')
      ->fields('w', ['wid'])
      ->orderBy('wid', 'ASC')
      ->range($initial, $count);
    $result = $query->execute();

    foreach ($result as $dblog) {

    }

  }

  /**
   * Initializes error capture for a path request.
   *
   * @param string $path
   *   The URL being tested.
   */
  protected function setErrorStorage($path) {
    $this->errorCount[$path] = [
      'response' => NULL,
      'initial' => $this->getWatchdogCount(),
      'final' => 0,
      'errors' => [],
    ];
  }

  /**
   * Returns the highwater row in the {watchdog} table.
   *
   * We do this so we can query the errors specific to a path.
   *
   * @return integer
   *   The count.
   */
  protected function getWatchdogCount() {
    $query = $this->database->select('watchdog', 'w')
      ->fields('w', ['wid'])
      ->orderBy('wid', 'DESC')
      ->range(0, 1);
    return $query->execute()->fetchField();
  }

  /**
   * Sets up the crawler run by changing application state.
   *
   * We want dblog enabled and full error reporting. When finished, we will
   * set those back.
   */
  private function setUp() {
    $state_change = [
      'error_level' => NULL,
      'dblog' => FALSE,
    ];

    // Set up error logging to highest level.
    $config = $this->configFactory->getEditable('system.logging');
    $error_level = $config->get('error_level');
    if ($error_level !== "all") {
      $config->set('error_level', 'all')->save();
      $state_change['error_level'] = $error_level;
    }

    // Ensure dblog is enabled.
    if (!$this->moduleHandler->moduleExists("dblog")) {
      $this->moduleInstaller->install(['dblog']);
      $state_change['dblog'] = TRUE;
    }

    // Set values in state.
    $this->state->set('tester', $state_change);
  }

  /**
   * Tears down the crawler run by changing application state.
   */
  private function tearDown() {
    $state_change = $this->state->get('tester');

    // Reset error reporting.
    if (!is_null($state_change['error_level'])) {
      $config = $this->configFactory->getEditable('system.logging');
      $config->set('error_level', $state_change['error_level'])->save();
    }

    // Reset dblog.
    if ($state_change['dblog']) {
      $this->moduleInstaller->uninstall(['dblog']);
    }

    // Clear state.
    $this->state->delete('tester');
  }

}
