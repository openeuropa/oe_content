<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create call for proposals content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class CallForProposalsContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_call_proposals)
   */
  public function alterCallForProposalsFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Title' => 'title',
      'Body text' => 'body',
      'Introduction' => 'oe_summary',
      'Publication date' => 'oe_publication_date',
      'Reference' => 'oe_reference_code',
      'Opening date' => 'oe_call_proposals_opening_date',
      'Deadline model' => 'oe_call_proposals_model',
      'Deadline date' => 'oe_call_proposals_deadline',
      'Awarded grants' => 'oe_call_proposals_grants',
      'Publication in the official journal' => 'oe_call_proposals_journal',
      'Alternative title' => 'oe_content_short_title',
      'Documents' => 'oe_documents',
      'Contact' => 'oe_call_proposals_contact',
      'Responsible department' => 'oe_departments',
      'Published' => 'status',
      'Funding programme' => 'oe_call_proposals_funding',
      'Teaser' => 'oe_teaser',
      'Subject' => 'oe_subject',
    ];

    foreach ($scope->getFields() as $key => $value) {
      $field_config = NULL;
      if (isset($mapping[$key])) {
        $field_config = FieldConfig::loadByName($scope->getEntityType(), $scope->getBundle(), $mapping[$key]);
      }
      switch ($key) {
        // Set SKOS Concept entity reference fields.
        case 'Responsible department':
        case 'Funding programme':
        case 'Subject':
          $fields = $this->getReferenceField($field_config, 'skos_concept', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set Media entity reference fields.
        case 'Documents':
          $fields = $this->getReferenceField($field_config, 'media', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Convert dates to UTC so that they can be expressed in site timezone.
        case 'Deadline date':
          $date = DrupalDateTime::createFromFormat(DateTimePlus::FORMAT, $value)
            ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, [
              'timezone' => DateTimeItemInterface::STORAGE_TIMEZONE,
            ]);
          $scope->addFields([$mapping[$key] => $date])->removeField($key);
          break;

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
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
    ]);
  }

}
