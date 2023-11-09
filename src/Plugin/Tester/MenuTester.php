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
  public function urls(array $options) {
    $urls = [];
    // @todo Figure out how to inject this service.
    $storage = \Drupal::menuTree();
    $parameters = new MenuTreeParameters;
    // Normalize menu options to array.
    $options['menus'] = explode(',', $options['menus']);

    foreach ($options['menus'] as $menu) {
      $tree = $storage->load(trim($menu), $parameters);

      $manipulators = [
        [
          'callable' => 'menu.default_tree_manipulators:generateIndexAndSort',
        ],
      ];
      $tree = $storage->transform($tree, $manipulators);

      $this->buildUrls($tree, $urls);

    }

    if ($options['limit'] > 0 && count($urls) >= $options['limit']) {
      $urls = array_slice($urls, 0, $options['limit']);
    }

    return $urls;
  }

  /**
   * Creates a recursive URL generator.
   *
   * Each item in a menu tree may itself be a menu tree, so we loop through
   * each one and then check for the presence of a `subtree`.
   *
   * @param array $tree
   *   The menu tree.
   * @param array $urls
   *   The array of urls to test.
   */
  public function buildUrls($tree, array &$urls) {
    foreach ($tree as $element) {
      $link = $element->link;
      $url = $link->getUrlObject();
      if (!$url->isExternal() && $link->isEnabled()) {
        $string = $url->toString();
        // Ignore any token paths, which break logins.
        // And explicitly do not log out.
        if (!str_contains($string, '?token=') && !str_contains($string, 'user/logout') ) {
          $urls[] = $string;
        }
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
