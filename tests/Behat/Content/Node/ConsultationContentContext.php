<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Content\Traits\GatherSubEntityContextTrait;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create consultation content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class ConsultationContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;
  use GatherSubEntityContextTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_consultation)
   */
  public function alterConsultationFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Alternative title' => 'oe_content_short_title',
      'Consultation outcome' => 'oe_consultation_outcome',
      'Contacts' => 'oe_consultation_contacts',
      'Deadline' => 'oe_consultation_deadline',
      'Departments' => 'oe_departments',
      'Documents' => 'oe_consultation_documents',
      'Introduction' => 'oe_summary',
      'Legal notice' => 'oe_consultation_legal_info',
      'Opening date' => 'oe_consultation_opening_date',
      'Outcome files' => 'oe_consultation_outcome_files',
      'Respond button' => 'oe_consultation_response_button',
      'Respond to the consultation' => 'oe_consultation_guidelines',
      'Closed status text' => 'oe_consultation_closed_text',
      'Target audience' => 'oe_consultation_target_audience',
      'Teaser' => 'oe_teaser',
      'Title' => 'title',
      'Why we are consulting' => 'oe_consultation_aim',
      'Navigation title' => 'oe_content_navigation_title',
      'Published' => 'status',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        case 'Contacts':
          $fields = $this->getReferenceRevisionField($mapping[$key], 'oe_contact', $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set entity reference fields.
        case 'Departments':
        case 'Outcome files':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Set Document reference entity reference fields.
        case 'Documents':
          $ids = [];
          $revision_ids = [];
          $names = explode(', ', $value);
          foreach ($names as $name) {
            $entity = $this->subEntityContext->getSubEntityByName($name);
            $ids[] = $entity->id();
            $revision_ids[] = $entity->getRevisionId();
          }
          $scope->addFields([
            $mapping[$key] . ':target_id' => implode(',', $ids),
            $mapping[$key] . ':target_revision_id' => implode(',', $revision_ids),
          ]);
          $scope->removeField($key);
          break;

        // Convert dates to UTC so that they can be expressed in site timezone.
        case 'Deadline':
          $date = DrupalDateTime::createFromFormat(DateTimePlus::FORMAT, $value)
            ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, [
              'timezone' => DateTimeItemInterface::STORAGE_TIMEZONE,
            ]);
          $scope->addFields([$mapping[$key] => $date])->removeField($key);
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
