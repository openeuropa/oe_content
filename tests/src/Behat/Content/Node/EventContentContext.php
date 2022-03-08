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
 * Context to create event content entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class EventContentContext extends RawDrupalContext {

  use EntityReferenceRevisionTrait;
  use EntityReferenceTrait;
  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @BeforeParseEntityFields(node,oe_event)
   */
  public function alterEventFields(BeforeParseEntityFieldsScope $scope): void {
    // Map human readable field names to their Behat parsable machine names.
    $mapping = [
      'Title' => 'title',
      'Type' => 'oe_event_type',
      'Introduction' => 'oe_summary',
      'Description summary' => 'oe_event_description_summary',
      'Description' => 'body',
      'Featured media legend' => 'oe_event_featured_media_legend',
      'Summary for report' => 'oe_event_report_summary',
      'Report text' => 'oe_event_report_text',
      'Start date' => 'oe_event_dates:value',
      'End date' => 'oe_event_dates:end_value',
      'Registration start date' => 'oe_event_registration_dates:value',
      'Registration end date' => 'oe_event_registration_dates:end_value',
      'Registration URL' => 'oe_event_registration_url:uri',
      'Registration capacity' => 'oe_event_registration_capacity',
      'Entrance fee' => 'oe_event_entrance_fee',
      'Online type' => 'oe_event_online_type',
      'Online time start' => 'oe_event_online_dates:value',
      'Online time end' => 'oe_event_online_dates:end_value',
      'Online description' => 'oe_event_online_description',
      'Online link' => 'oe_event_online_link',
      'Languages' => 'oe_event_languages',
      'Internal organiser' => 'oe_event_organiser_internal',
      'Featured media' => 'oe_event_featured_media',
      'Status' => 'oe_event_status',
      'Organiser is internal' => 'oe_event_organiser_is_internal',
      'Organiser name' => 'oe_event_organiser_name',
      'Event website' => 'oe_event_website',
      'Social media links' => 'oe_social_media_links',
      'Teaser' => 'oe_teaser',
      'Venue' => 'oe_event_venue',
      'Contact' => 'oe_event_contact',
    ];

    foreach ($scope->getFields() as $key => $value) {
      // Handle entity references.
      switch ($key) {
        case 'Venue':
        case 'Contact':
          $fields = $this->getReferenceRevisionField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        case 'Organiser is internal':
          $scope->addFields([
            $mapping[$key] => (int) ($value === 'Yes'),
          ])->removeField($key);
          break;

        case 'Type':
        case 'Languages':
        case 'Internal organiser':
        case 'Featured media':
          $fields = $this->getReferenceField($scope->getEntityType(), $scope->getBundle(), $mapping[$key], $value);
          $scope->addFields($fields)->removeField($key);
          break;

        // Convert dates to UTC so that they can be expressed in site timezone.
        case 'Start date':
        case 'End date':
        case 'Registration start date':
        case 'Registration end date':
        case 'Online time start':
        case 'Online time end':
          $date = DrupalDateTime::createFromFormat(DateTimePlus::FORMAT, $value)
            ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, [
              'timezone' => DateTimeItemInterface::STORAGE_TIMEZONE,
            ]);
          $scope->addFields([$mapping[$key] => $date])->removeField($key);
          break;

        default:
          if (isset($mapping[$key])) {
            $scope->renameField($key, $mapping[$key]);
          }
      }
    }

    // Set default fields.
    $scope->addFields([
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ]);
  }

}
