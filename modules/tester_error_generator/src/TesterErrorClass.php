<?php

namespace Drupal\tester_error_generator;

/**
 * This class deliberately throws PHP 8.2 warnings.
 */
class TesterErrorClass {

  /**
   * Sets a dynamic property to trigger a warning.
   */
  public function testDynamicProperty(): void {
    $this->value = 'dynamic property error';
  }

}
