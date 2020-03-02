<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Contact;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;

/**
 * Context to create general contact corporate entities.
 */
class GeneralContactContext extends RawDrupalContext {

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @BeforeParseEntityFields(oe_contact,oe_general)
   */
  public function alterGeneralContactFields(BeforeParseEntityFieldsScope $scope): void {
    $fields = [];

    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Name' => 'name',
      'Address' => 'oe_address',
      'Email' => 'oe_email',
      'Phone number' => 'oe_phone',
    ];
    foreach ($scope->getFields() as $key => $value) {
      $key = $mapping[$key] ?? $key;
      $fields[$key] = $value;
    }

    $scope->setFields($fields);
  }

}
