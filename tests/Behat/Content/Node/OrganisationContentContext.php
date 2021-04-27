<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create organisation content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class OrganisationContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_organisation)
   */
  public function alterOrganisation(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Alternative title' => 'oe_content_short_title',
      'Body text' => 'body',
      'Organisation type' => 'oe_organisation_org_type',
      'EU organisation' => 'oe_organisation_eu_org',
      'Non-EU organisation' => 'oe_organisation_non_eu_org_type',
      'Published' => 'status',
      'Acronym' => 'oe_organisation_acronym',
      'Contact' => 'oe_organisation_contact',
      'Logo' => 'oe_organisation_logo',
      'Introduction' => 'oe_summary',
      'Teaser' => 'oe_teaser',
      'Title' => 'title',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'EU organisation':
        case 'Non-EU organisation':
        case 'Logo':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set Contact entity reference field.
        case 'Contact':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_contact', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set content published status.
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

    // Set default fields.
    $scope->addFields([
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);
  }

}
