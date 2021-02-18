<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\PersonJob;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create default person job entities.
 */
class DefaultPersonJobContext extends RawDrupalContext {

  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat scope.
   *
   * @BeforeParseEntityFields(oe_person_job,default)
   */
  public function alterPersonJobFields(BeforeParseEntityFieldsScope $scope): void {
    // Maps human readable field names to their Behat parsable machine names.
    $mapping = [
      'Role name' => 'oe_role_name',
      'Role reference' => 'oe_role_reference',
      'Acting role' => 'oe_acting',
      'Responsibilities' => 'oe_description',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'Role reference':
          $fields = $this->getReferenceField($mapping[$key], 'skos_concept', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Acting role':
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
