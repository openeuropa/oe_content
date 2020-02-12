<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;

/**
 * Context to create event corporate entities.
 */
class EventContentContext extends RawDrupalContext {

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
      'Start date' => 'oe_event_dates:value',
      'End date' => 'oe_event_dates:end_value',
    ];
    foreach ($scope->getFields() as $key => $value) {
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
