<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create publication content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class PublicationContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_publication)
   */
  public function alterPublicationFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Alternative title' => 'oe_content_short_title',
      'Body text' => 'body',
      'Related department' => 'oe_departments',
      'Author' => 'oe_author',
      'Last update date' => 'oe_publication_last_updated',
      'Publication date' => 'oe_publication_date',
      'Published' => 'status',
      'Resource type' => 'oe_publication_type',
      'Thumbnail' => 'oe_publication_thumbnail',
      'Country' => 'oe_publication_countries',
      'Contact' => 'oe_publication_contacts',
      'Identifier code' => 'oe_reference_codes',
      'Files' => 'oe_documents',
      'Introduction' => 'oe_summary',
      'Teaser' => 'oe_teaser',
      'Title' => 'title',
      'Publications' => 'oe_publication_publications',
      'Collection' => 'oe_publication_collection',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'Author':
        case 'Country':
        case 'Related department':
        case 'Resource type':
        case 'Files':
        case 'Thumbnail':
        case 'Publications':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set Contact entity reference field.
        case 'Contact':
          $fields = $this->getReferenceRevisionField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set content published and collection status.
        case 'Published':
        case 'Collection':
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
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ACER',
    ]);
  }

}
