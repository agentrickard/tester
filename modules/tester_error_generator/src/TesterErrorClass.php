<?php

namespace Drupal\tester_error_generator;

/**
 * This class deliberately throws PHP 8.2 warnings.
 */
class TesterErrorClass {

  /**
   * Sets a dynamic property, value.
   *
   * @return void
   */
  public function testDynamicProperty() {
    $this->value = 'dynamic property error';
  }

}
