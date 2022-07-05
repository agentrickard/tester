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
  public function urls($limit) {
    $urls = [];

    // @todo Are there more anon system urls?
    $list = [
      '/',
      '/admin',
      '/foo-bar',
    ];

    foreach ($list as $item) {
      if ($limit > 0 && count($urls) >= $limit) {
        break;
      }
      $urls[] = $item;
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
