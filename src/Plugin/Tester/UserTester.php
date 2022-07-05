<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the User module.
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
  public function urls(array $options) {
    // @todo Figure out how to inject this service.
    $storage = \Drupal::entityTypeManager()->getStorage('user');
    $users = $storage->loadMultiple();

    $urls = [];
    foreach ($users as $user) {
      $urls[] = $user->toUrl()->toString();
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
        'user',
      ],
    ];
  }

}
