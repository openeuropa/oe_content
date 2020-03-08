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

/**
 * Context to create event corporate entities.
 *
 * @SuppressWarnings(PHPMD)
 */
class EventContentContext extends RawDrupalContext {

  use EntityLoadingTrait;
  use EntityReferenceRevisionTrait;

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
      'Status' => 'oe_event_status',
      'Organiser name' => 'oe_event_organiser_name',
      'Event website' => 'oe_event_website',
      'Social media links' => 'oe_social_media_links',
    ];

    $fields = $scope->getFields();
    foreach ($fields as $key => $value) {

      // Handle entity references.
      switch ($key) {
        case 'Venue':
          $fields += $this->getReferenceRevisionField('oe_event_venue', 'oe_venue', $value);
          break;

        case 'Partner':
          $fields += $this->getReferenceRevisionField('oe_event_partner', 'oe_organisation', $value);
          break;

        case 'Contact':
          $fields += $this->getReferenceRevisionField('oe_event_contact', 'oe_contact', $value);
          break;

        case 'Featured media':
          $fields['oe_event_featured_media:target_id'] = $this->loadEntityByLabel('media', $value)->id();
          break;

        case 'Organiser is internal':
          $fields['oe_event_organiser_is_internal'] = (int) ($value === 'Yes');
          break;

        case 'Internal organiser':
          $fields['oe_event_organiser_internal'] = $this->loadEntityByLabel('skos_concept', $value)->id();
          break;

        // Convert dates to UTC so that they can be expressed in site timezone.
        case 'Start date':
        case 'End date':
        case 'Registration start date':
        case 'Registration end date':
        case 'Online time start':
        case 'Online time end':
          $date = DrupalDateTime::createFromFormat(DateTimePlus::FORMAT, $value);
          $fields[$mapping[$key]] = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, [
            'timezone' => DateTimeItemInterface::STORAGE_TIMEZONE,
          ]);
          break;

        default:
          $key = $mapping[$key] ?? $key;
          $fields[$key] = $value;
      }
    }

    // Set default fields.
    $fields += [
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ];
    $scope->setFields($fields);
  }

}
