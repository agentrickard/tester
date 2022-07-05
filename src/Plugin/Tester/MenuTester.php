<?php

namespace Drupal\tester\Plugin\Tester;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\tester\Plugin\TesterPluginInterface;

/**
 * Defines routes owned by the Node module.
 *
 * @TesterPlugin(
 *   id = "menu",
 * )
 *
 */
class MenuTester extends PluginBase implements TesterPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function urls($limit) {
    // @todo Figure out how to inject this service.
    $storage = \Drupal::menuTree();
    $parameters = new MenuTreeParameters;
    $tree = $storage->load('main', $parameters);

    $manipulators = [
      [
        'callable' => 'menu.default_tree_manipulators:generateIndexAndSort',
      ],
    ];
    $tree = $storage->transform($tree, $manipulators);

    $urls = [];
    $this->buildUrls($tree, $urls);

    if ($limit > 0 && count($urls) >= $limit) {
      $urls = array_slice($urls, 0, $limit);
    }

    return $urls;
  }

  /**
   * Recursive URL generator
   * @param array $urls
   */
  public function buildUrls($tree, array &$urls) {
    foreach ($tree as $element) {
      $link = $element->link;
      $url = $link->getUrlObject();
      if (!$url->isExternal() && $link->isEnabled()) {
        $urls[] = $url->toString();
      }
      if ($element->subtree) {
        $this->buildUrls($element->subtree, $urls);
      }
    }
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
