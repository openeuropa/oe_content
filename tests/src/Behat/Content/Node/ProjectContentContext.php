<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create project content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class ProjectContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_project)
   */
  public function alterProjectFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Alternative title' => 'oe_content_short_title',
      'Body text' => 'body',
      'Call for proposals' => 'oe_project_calls',
      'Coordinators' => 'oe_project_coordinators',
      'Departments' => 'oe_departments',
      'EU contribution' => 'oe_project_eu_contrib',
      'Featured media' => 'oe_featured_media',
      'Funding programme' => 'oe_project_funding_programme',
      'Navigation title' => 'oe_content_navigation_title',
      'Overall budget' => 'oe_project_eu_budget',
      'Participants' => 'oe_project_participants',
      'Project contact' => 'oe_project_contact',
      'Project locations' => 'oe_project_locations',
      'Project period start date' => 'oe_project_dates:value',
      'Project period end date' => 'oe_project_dates:end_value',
      'Published' => 'status',
      'Reference' => 'oe_reference_code',
      'Documents' => 'oe_documents',
      'Result files' => 'oe_project_result_files',
      'Results' => 'oe_project_results',
      'Summary' => 'oe_summary',
      'Teaser' => 'oe_teaser',
      'Title' => 'title',
      'Website' => 'oe_project_website',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'Departments':
        case 'Funding programme':
        case 'Documents':
        case 'Featured media':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set entity_reference_revisions fields.
        case 'Result files':
        case 'Coordinators':
        case 'Participants':
        case 'Project contact':
          $fields = $this->getReferenceRevisionField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
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
      'oe_subject' => 'http://data.europa.eu/uxp/1010',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);
  }

}
