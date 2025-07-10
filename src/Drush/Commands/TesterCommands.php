<?php

namespace Drupal\tester\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\tester\TesterPluginManager;
use Drupal\user\Entity\User;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Cookie\CookieJar;


/**
 * Defines the class for our drush commands.
 */
class TesterCommands extends DrushCommands {

  use StringTranslationTrait;

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
   * The state service.
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The error count for the run.
   *
   * @var array
   */
  protected $errorLog = [];

  /**
   * The admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

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
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The http client factory service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(TesterPluginManager $plugin_manager, ModuleHandlerInterface $module_handler, ModuleInstallerInterface $module_installer, ClientFactory $http_client_factory, ConfigFactoryInterface $config_factory, StateInterface $state, Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    $this->pluginManager = $plugin_manager;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client_factory->fromOptions(['cookies' => TRUE, 'allow_redirects' => TRUE]);
    $this->state = $state;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('plugin.manager.tester'),
      $container->get('module_handler'),
      $container->get('module_installer'),
      $container->get('http_client_factory'),
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Lists valid plugins.
   *
   * @command tester:list
   * @aliases tcl
   * @usage drush tester:list
   */
  public function listPlugins() {
    $list = $this->chooseOptions();
    unset($list['all']);
    unset($list['cancel']);
    $this->io()->title($this->t('Avaliable plugins:'));
    return $this->io()->listing($list);
  }

  /**
   * Crawls a site looking for errors.
   *
   * @param string $base_url
   *   The base URL to use when crawling the site. No trailing slash.
   *   If not provided, the global $base_url value will be used.
   *
   * @option test
   *   The test to run (optional). Pass `--test=all` to run all tests.
   * @option limit
   *   The number of urls to crawl for _each_ plugin. Pass 0 to crawl all urls.
   *   Default value is 500.
   * @option menus
   *   The menus to crawl as a comma-separated list. If blank, the tester will
   *   crawl the `admin`, `main` and `footer` menus (optional).
   * @option admin
   *   Boolean flag to log in with user `admin` and password `admin` (optional).
   * @option user
   *   The user name to log in with prior to the test (optional).
   * @option password
   *   The user password to log in with prior to the test (required if --user is set).
   * @option errors
   *   If set, only errors will be reported.
   * @option verify
   *   If set, the site must have a valid SSL certificate.
   *
   * @command tester:crawl
   * @aliases tester-crawl, tc
   * @usage drush tester:crawl, drush tc
   * @usage drush tester:crawl --test=all
   * @usage drush tester:crawl --test=all --limit=10
   * @usage drush tester:crawl --test=all --limit=10 --menus=main,header
   * @usage drush tester:crawl --test=all --limit=10 --menus=admin,main,header --admin
   * @usage drush tester:crawl --test=all --limit=10 --menus=admin,main,header --user=[USERNAME] --password=[PASSWORD]
   * @usage drush tester:crawl example.com
   * @usage drush tester:crawl example.com --test=node
   * @usage drush tester:crawl example.com --test=node --errors
   *
   * @field-labels
   *   path: Path
   *   status: Status
   *   errors: Errors
   * @default_fields path,status,errors
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Table output.
   */
  public function crawl($base_url = NULL, array $options = ['test' => NULL, 'limit' => 500, 'menus' => 'main,footer,admin', 'admin' => FALSE, 'user' => NULL, 'password' => NULL, 'errors' => FALSE, 'verify' => FALSE]) {
    $rows = [];
    $this->adminUser = $this->entityTypeManager->getStorage('user')->load(1);
    $this->setUp();

    if (is_null($options['test'])) {
      $select = $this->chooseOptions();
      $options['test'] = $this->io()->choice($this->t('Select the tests to run'), $select);
    }

    if ($options['test'] === 'cancel') {
      return $this->io()->success($this->t('Operation cancelled.'));
    }

    $this->io()->title("Crawling URLs");

    if (is_null($base_url)) {
      global $base_url;
    }

    $urls = array_unique($this->getUrls($options));

    // We want to test 403 and 404 pages, so allow them.
    // See https://docs.guzzlephp.org/en/stable/request-options.html#http-errors
    $cookie_jar = new CookieJar();
    $options += [
      'http_errors' => FALSE,
      'cookies' => $cookie_jar,
    ];
    // Set an explicit http version for the request.
    if (!$options['version']) {
      $options['version'] = 1.1;
    }
    $error_count = 0;
    if (empty($urls)) {
      return $this->io()->error($this->t('No valid plugins were found.'));
    }
    else {
      // Login, if requested.
      if ($options['admin']) {
        $account = User::load(1);
        $timestamp = \Drupal::time()->getRequestTime();
        $link = Url::fromRoute(
          'user.reset.login',
          [
            'uid' => $account->id(),
            'timestamp' => $timestamp,
            'hash' => user_pass_rehash($account, $timestamp),
          ],
          [
            'absolute' => TRUE,
            'query' => [],
            'language' => \Drupal::languageManager()->getLanguage($account->getPreferredLangcode()),
            'https' => FALSE,
          ]
        )->toString();
        $this->httpClient->request('GET', $link, $options);
      }
      elseif ($options['user'] && $options['password']) {
        $this->io()->success($this->t("Logging in as '" . $options['user'] . "' using password..."));
        $options['form_params'] = [
          'name' => $options['user'],
          'pass' => $options['password'],
          'form_id' => 'user_login_form',
        ];
        $this->httpClient->request('POST', $base_url . '/user/login', $options);
        unset($options['form_params']);
      }
      $this->io()->progressStart(count($urls));
      foreach ($urls as $url) {
        $path = $base_url . $url;
        if (!$options['verify']) {
          $path = str_replace('https:', 'http:', $path);
        }

        $this->setErrorStorage($path);
        $response = $this->httpClient->request('GET', $path, $options);
        $this->io()->progressAdvance();

        $this->setErrorLog($path, ['response' => $response->getStatusCode()]);
        $this->captureErrors($path);

        // @todo Move to a render function?
        // @todo Alternate formatting?
        $errors = $this->getErrorLog($path, 'count') ?: 0;
        if (!$options['errors'] || $errors > 0) {
          $row = [
            'path' => $path,
            'status' => $this->getErrorLog($path, 'response'),
            'errors' => $errors,
          ];
          $rows[] = $row;

          if ($row['errors']) {
            $rows[]['path'] = '';
            foreach ($this->getErrorLog($path, 'errors') as $error) {
              $error_count++;
              $rows[] = [
                'path' => ' • ' . trim(strip_tags($error), "."),
              ];
            }
            $rows[]['path'] = '';
          }
        }
      }

      $this->io()->progressFinish();
    }

    $this->tearDown($options['admin']);

    $message = $this->t('Tested @count urls and found @error_count errors.', [
      '@count' => count($urls),
      '@error_count' => $error_count,
    ]);

    if ($error_count > 0) {
      $this->io()->error($message);
    }
    else {
      $this->io()->success($message);
    }

    return new RowsOfFields($rows);
  }

  /**
   * Chooses the plugins to run during a crawl.
   */
  public function chooseOptions() {
    $options['all'] = $this->t('all');

    $plugins = $this->pluginManager->getDefinitions();

    foreach (array_keys($plugins) as $id) {
      $instance = $this->pluginManager->createInstance($id);
      $dependencies = $instance->dependencies();
      if ($this->isAllowed($dependencies)) {
        $id = $instance->getPluginId();
        $options[$id] = $id;
      }
    }

    $options['cancel'] = $this->t('cancel');
    return $options;
  }

  /**
   * Retrieves the list of URLs to test.
   *
   * @param array $options
   *   The options passed to the command.
   *
   * @return array
   *   An array of URLs.
   */
  private function getUrls(array $options) {
    $urls = [];

    $plugins = $this->pluginManager->getDefinitions();

    foreach (array_keys($plugins) as $id) {
      if ($options['test'] !== 'all' && $id !== $options['test']) {
        continue;
      }
      $instance = $this->pluginManager->createInstance($id);
      $dependencies = $instance->dependencies();
      if ($this->isAllowed($dependencies)) {
        $urls = array_merge($urls, $instance->urls($options));
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
          foreach ($extensions as $extension) {
            if (!$this->moduleHandler->moduleExists($extension)) {
              $return = FALSE;
            }
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
    $final = $this->getWatchdogCount();
    $initial = $this->getErrorLog($path, 'initial');

    if ($final > $initial) {
      $count = $final - $initial;
      $errors = $this->getErrors($count, $initial);
      $this->setErrorLog($path, [
        'final' => $final,
        'count' => count($errors),
        'errors' => $errors,
      ]);
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
    $errors = [];
    // We cannot filter by type accurately?
    $query = $this->database->select('watchdog', 'w')
      ->fields('w', ['wid', 'message', 'variables', 'type'])
      ->orderBy('wid', 'ASC')
      ->range($initial, $count);
    $result = $query->execute();

    foreach ($result as $dblog) {
      if ($dblog->type === 'php') {
        $errors[$dblog->wid] = $this->formatMessage($dblog);
      }
    }

    return $errors;
  }

  /**
   * Initializes error capture for a path request.
   *
   * @param string $path
   *   The URL being tested.
   */
  protected function setErrorStorage($path) {
    $data = [
      'response' => NULL,
      'initial' => $this->getWatchdogCount(),
      'final' => 0,
      'count' => 0,
      'errors' => [],
    ];
    $this->setErrorLog($path, $data);
  }

  /**
   * Returns an error log value for a specific path.
   *
   * @param $path
   *   The path being checked.
   * @param $value
   *   The value to retrieve.
   *
   * @return mixed|null
   */
  public function getErrorLog($path, $value) {
    $return = NULL;

    // The default returns must match set types.
    switch ($value) {
      case 'response':
        $return = $this->errorLog[$path][$value] ?: NULL;
        break;

      case 'errors':
        $return = $this->errorLog[$path][$value] ?: [];
        break;

      default:
        $return = $this->errorLog[$path][$value] ?: 0;
        break;
    }

    return $return;
  }

  /**
   * Sets the error log for a request.
   *
   * @param $path
   *   The path being checked.
   * @param array $values
   *   The values to set. Only pass what has changed.
   *
   * @return array
   */
  public function setErrorLog($path, array $values) {
    foreach ($values as $key => $value) {
      $this->errorLog[$path][$key] = $value;
    }

    return $this->errorLog;
  }

  /**
   * Returns the highwater row in the {watchdog} table.
   *
   * We do this so we can query the errors specific to a path.
   *
   * @return int
   *   The count.
   */
  protected function getWatchdogCount() {
    $query = $this->database->select('watchdog', 'w')
      ->fields('w', ['wid'])
      ->condition('w.type', 'php')
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

  /**
   * Formats a database log message.
   *
   * @param object $row
   *   The record from the watchdog table. The object properties are: wid, uid,
   *   severity, type, timestamp, message, variables, link, name.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup|false
   *   The formatted log message or FALSE if the message or variables properties
   *   are not set.
   */
  public function formatMessage($row) {
    // Check for required properties.
    if (isset($row->message, $row->variables)) {
      $variables = @unserialize($row->variables);
      // Messages without variables or user specified text.
      if ($variables === NULL) {
        $message = Xss::filterAdmin($row->message);
      }
      elseif (!is_array($variables)) {
        $message = $this->t('Log data is corrupted and cannot be unserialized: @message', ['@message' => Xss::filterAdmin($row->message)]);
      }
      // Message to translate with injected variables.
      else {
        // We deliberately suppress the backtrace.
        $variables['@backtrace_string'] = "";
        $message = $this->t(Xss::filterAdmin($row->message), $variables);
      }
    }
    else {
      $message = FALSE;
    }
    return $message;
  }

}
