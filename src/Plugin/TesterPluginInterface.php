<?php

namespace Drupal\tester\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface TesterPluginInterface extends PluginInspectionInterface {

  /**
   * Returns an array of URLs for testing.
   *
   * @return array
   *   The URL path, with a leading slash (e.g. /node/3).
   */
  public function urls();

  /**
   * Returns an array of dependencies.
   *
   * This is a nested array, where the top-level is the dependency type
   * (e.g. "module" or "theme"). Each type can then declare an array of
   * dependencies. Normally, we expect `modules`.
   *
   * @return array
   */
  public function dependencies();
}
