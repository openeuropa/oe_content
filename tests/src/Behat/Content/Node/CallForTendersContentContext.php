<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;

/**
 * Context to create call for tenders content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class CallForTendersContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_call_tenders)
   */
  public function alterCallForTendersFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Alternative title' => 'oe_content_short_title',
      'Body text' => 'body',
      'Responsible department' => 'oe_departments',
      'Opening of tenders' => 'oe_call_tenders_opening_date',
      'Deadline date' => 'oe_call_tenders_deadline',
      'Publication date' => 'oe_publication_date',
      'Published' => 'status',
      'Reference' => 'oe_reference_code',
      'Documents' => 'oe_documents',
      'Introduction' => 'oe_summary',
      'Teaser' => 'oe_teaser',
      'Title' => 'title',
    ];

    foreach ($scope->getFields() as $key => $value) {
      switch ($key) {
        // Set entity reference fields.
        case 'Responsible department':
        case 'Documents':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
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
