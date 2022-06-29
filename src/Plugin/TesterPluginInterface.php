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

}
