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
 */
class SystemTester extends PluginBase implements TesterPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function urls(array $options) {
    // @todo Are there more anon system urls?
    $urls = [
      '',
      '/',
      '/system/401',
      '/system/403',
      '/admin',
      '/system/404',
      '/foo-bar-baz',
    ];

    if ($options['limit'] > 0 && count($urls) >= $options['limit']) {
      $urls = array_slice($urls, 0, $options['limit']);
    }

    return $urls;
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
