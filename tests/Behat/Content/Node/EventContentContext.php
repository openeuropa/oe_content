<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Drupal\Tests\oe_content\Behat\Content\RawCorporateContentContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create event corporate entities.
 */
class EventContentContext extends RawCorporateContentContext {

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
      'Description summary' => 'oe_event_description_summary',
      'Description' => 'body',
      'Featured media' => 'oe_event_featured_media:target_id',
      'Featured media legend' => 'oe_event_featured_media_legend',
      'Summary for report' => 'oe_event_report_summary',
      'Report text' => 'oe_event_report_text',
      'Start date' => 'oe_event_dates:value',
      'End date' => 'oe_event_dates:end_value',
      'Registration start date' => 'oe_event_registration_dates:value',
      'Registration end date' => 'oe_event_registration_dates:end_value',
      'Registration URL' => 'oe_event_registration_url:uri',
      'Registration entrance fee' => 'oe_event_entrance_fee',
      'Registration capacity' => 'oe_event_registration_capacity',
      'Online type' => 'oe_event_online_type',
      'Online time start' => 'oe_event_online_dates:value',
      'Online time end' => 'oe_event_online_dates:end_value',
      'Online description' => 'oe_event_online_description',
      'Online link' => 'oe_event_online_link',
      'Languages' => 'oe_event_languages',
      'Status' => 'oe_event_status',
      'Organiser name' => 'oe_event_organiser_name',
      'Event website' => 'oe_event_website',
      'Venue' => 'oe_event_venue:target_id',
      'Contact' => 'oe_event_contact:target_id',
      'Partner' => 'oe_event_partner:target_id',
      'Social media links' => 'oe_social_media_links',
    ];
    foreach ($scope->getFields() as $key => $value) {

      // Handle entity references.
      switch ($key) {
        case 'Venue':
          $venue = $this->loadEntityByLabel('oe_venue', $value, 'oe_default');
          $value = $venue->id();
          // For revision reference fields we have give the target_revision_id.
          $fields['oe_event_venue:target_revision_id'] = $venue->getRevisionId();

          break;
        case 'Contact':
          // Transform titles to ids and maintain the format of comma separated.
          $items = strpos($value, ', ') ? $items = explode(', ', $value) : $value;
          $value = [];
          foreach ($items as $item) {
            $contact = $this->loadEntityByLabel('oe_contact', $item);
            $value[] = $contact->id();
            // For revision reference fields we have give the target_revision_id.
            $revision_ids[] = $contact->getRevisionId();
          }
          $value = implode(', ', $value);
          $fields['oe_event_contact:target_revision_id'] = implode(', ', $revision_ids);

          break;
        case 'Featured media':
          $value = $this->loadEntityByLabel('media', $value)->id();

          break;
      }

      $key = $mapping[$key] ?? $key;

      $fields[$key] = $value;
    }

    // Set default fields and return.
    $fields += [
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ];
    $scope->setFields($fields);
  }

}
