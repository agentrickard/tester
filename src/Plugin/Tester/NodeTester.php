<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the Node module.
 *
 * @TesterPlugin(
 *   id = "node",
 * )
 *
 */
class NodeTester extends PluginBase implements TesterPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function urls(array $options) {
    // @todo Figure out how to inject this service.
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadMultiple();

    $urls = [];
    foreach ($nodes as $node) {
      $urls[] = $node->toUrl()->toString();
    }

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
        'node',
        'user',
      ],
    ];
  }

}
