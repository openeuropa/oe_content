<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Organisation;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create stakeholder organisation corporate entities.
 */
class StakeholderOrganisationContext extends RawDrupalContext {

  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_organisation,oe_stakeholder)
   */
  public function alterStakeholderOrganisationFields(BeforeParseEntityFieldsScope $scope): void {
    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Name' => 'name',
      'Acronym' => 'oe_acronym',
      'Address' => 'oe_address',
      'Contact page URL' => 'oe_contact_url',
      'Logo' => 'oe_logo',
      'Website' => 'oe_website',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set Media entity reference fields.
        case 'Logo':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Published':
          $scope->addFields([
            $mapping[$key] => (int) ($value === 'Yes'),
          ])->removeField($key);
          break;

        default:
          if (isset($mapping[$key])) {
            $scope->renameField($key, $mapping[$key]);
          }
      }
    }
  }

}
