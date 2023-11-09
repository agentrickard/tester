<?php

namespace Drupal\tester;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Tester plugin manager.
 */
class TesterPluginManager extends DefaultPluginManager {

  /**
   * Constructs TesterPluginManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Tester', $namespaces, $module_handler, 'Drupal\tester\Plugin\TesterPluginInterface', 'Drupal\tester\Annotation\TesterPlugin');
    $this->setCacheBackend($cache_backend, 'tester_plugins');
    $this->alterInfo('tester_plugin');
  }

}
