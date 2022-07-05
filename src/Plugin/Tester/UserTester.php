<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the System module.
 *
 * @TesterPlugin(
 *   id = "user",
 * )
 *
 */
class UserTester extends PluginBase implements TesterPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function urls($limit) {
    // @todo Figure out how to inject this service.
    $storage = \Drupal::entityTypeManager()->getStorage('user');
    $users = $storage->loadMultiple();

    $urls = [];
    foreach ($users as $user) {
      if ($limit > 0 && count($urls) >= $limit) {
        break;
      }
      $urls[] = $user->toUrl()->toString();
    }

    return $urls;
  }

  /**
   * {@inheritdoc}
   */
  public function dependencies() {
    return [
      'modules' => [
        'user',
      ],
    ];
  }

}
