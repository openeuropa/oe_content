<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create event corporate entities.
 */
class EventContentContext extends RawDrupalContext {

  use EntityLoadingTrait;

  /**
   * Run before fields are parsed by Drupal Behat extension.
   *
   * @BeforeParseEntityFields(node,oe_event)
   */
  public function alterEventFields(BeforeParseEntityFieldsScope $scope): void {
    $fields = [];

    // Maps human readable field names to their Behat parsable machine names.
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
    foreach ($scope->getFields() as $key => $value) {

      // Handle entity references.
      switch ($key) {
        case 'Venue':
          // For revision reference fields we have give the target_revision_id.
          $entity = $this->loadEntityByLabel('oe_venue', $value, 'oe_default');
          $fields['oe_event_venue:target_id'] = $entity->id();
          $fields['oe_event_venue:target_revision_id'] = $entity->getRevisionId();
          break;

        case 'Partner':
          // For revision reference fields we have give the target_revision_id.
          $entity = $this->loadEntityByLabel('oe_organisation', $value, 'oe_default');
          $fields['oe_event_partner:target_id'] = $entity->id();
          $fields['oe_event_partner:target_revision_id'] = $entity->getRevisionId();
          break;

        case 'Contact':
          // Transform titles to ids and maintain the comma separated format.
          $items = explode(',', $value);
          $items = array_map('trim', $items);
          $ids = [];
          $revision_ids = [];
          foreach ($items as $item) {
            $entity = $this->loadEntityByLabel('oe_contact', $item);
            $ids[] = $entity->id();
            $revision_ids[] = $entity->getRevisionId();
          }

          // For revision reference field we have give the target_revision_id.
          $fields['oe_event_contact:target_id'] = implode(',', $ids);
          $fields['oe_event_contact:target_revision_id'] = implode(',', $revision_ids);
          break;

        case 'Featured media':
          $entity = $this->loadEntityByLabel('media', $value);
          $fields['oe_event_featured_media:target_id'] = $entity->id();
          break;

        case 'Organiser is internal':
          $fields['oe_event_organiser_is_internal'] = $value === 'Yes' ? 1 : 0;
          break;

        case 'Internal organiser':
          $entity = $this->loadEntityByLabel('skos_concept', $value);
          $fields['oe_event_organiser_internal'] = $entity->id();
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
