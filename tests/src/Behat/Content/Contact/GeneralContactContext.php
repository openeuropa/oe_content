<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content\Contact;

use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;

/**
 * Context to create general contact corporate entities.
 */
class GeneralContactContext extends ContactContext {

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_contact,oe_general)
   */
  public function alterGeneralContactFields(BeforeParseEntityFieldsScope $scope): void {
    $this->alterContactFields($scope);
  }

}
