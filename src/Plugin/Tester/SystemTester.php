<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the System module.
 *
 * @TesterPlugin(
 *   id = "system",
 * )
 *
 */
class SystemTester extends PluginBase implements TesterPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function urls() {
    return [
      '/',
      '/admin',
      '/foo-bar',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function dependencies() {
    return [
      'modules' => [
        'system',
      ],
    ];
  }

}
