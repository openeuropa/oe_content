<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Venue;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;

/**
 * Context to create venue corporate entities.
 */
class DefaultVenueContext extends RawDrupalContext {

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @BeforeParseEntityFields(oe_venue,oe_default)
   */
  public function alterVenueFields(BeforeParseEntityFieldsScope $scope): void {
    $fields = [];

    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Name' => 'name',
      'Address' => 'oe_address',
      'Capacity' => 'oe_capacity',
      'Room' => 'oe_room',
    ];
    foreach ($scope->getFields() as $key => $value) {
      $key = $mapping[$key] ?? $key;
      $fields[$key] = $value;
    }

    $scope->setFields($fields);
  }

}
