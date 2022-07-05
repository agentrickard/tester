<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the System module.
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
  public function urls($limit) {
    // @todo Figure out how to inject this service.
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadMultiple();

    $urls = [];
    foreach ($nodes as $node) {
      if ($limit > 0 && count($urls) >= $limit) {
        break;
      }
      $urls[] = $node->toUrl()->toString();
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
