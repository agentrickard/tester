<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;

/**
 * Defines routes owned by the System module.
 *
 * @TesterPlugin(
 *   id = "system"
 * )
 *
 */
class SystemTester extends PluginBase {

  public function urls() {
    return [
      '/',
      '/admin',
      '/foo-bar',
    ];
  }

}
