<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
 */
class CorporateContentContext extends RawDrupalContext {

  use EntityLoadingTrait;

  /**
   * Transform event table.
   *
   * @transform table:Event field,Value
   */
  public function transformEventFieldNames(TableNode $table): array {
    return $this->mapTableFields($table, [
      'Title' => 'title',
      'Type' => 'oe_event_type',
      'Description summary' => 'oe_event_description_summary',
      'Start date' => 'oe_event_dates:value',
      'End date' => 'oe_event_dates:end_value',
    ]);
  }

  /**
   * Provide default field values, including the entity bundle.
   *
   * This is useful to set common fields, such as subject, author, etc.
   *
   * @return array
   *   An array of default field values, keyed by their respective field name.
   */
  private function defaultFieldValues(): array {
    return [
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ];
  }

  /**
   * Create a node.
   *
   * @Given the following :bundle_label content:
   */
  public function createNode(string $bundle_label, array $fields): void {
    $bundle = $this->loadEntityByLabel('node_type', $bundle_label)->id();
    $fields['type'] = $bundle;
    $fields += $this->defaultFieldValues();

    $this->nodeCreate((object) $fields);
  }

  /**
   * Update a node.
   *
   * @Given the :bundle_label titled ":title" is updated as follow:
   */
  public function updateNode(string $bundle_label, string $title, array $fields): void {
    $bundle = $this->loadEntityByLabel('node_type', $bundle_label)->id();
    $node = $this->loadEntityByLabel('node', $title, $bundle);
    // We have to cast as parseEntityFields() expects an object passed by ref.
    $fields = (object) $fields;
    $this->parseEntityFields('node', (object) $fields);
    foreach ($fields as $name => $value) {
      $node->set($name, $value);
    }
    $node->save();
  }

  /**
   * Maps human readable field names to their Behat parsable machine names.
   *
   * This method can be used to to transform human readable field names into
   * a string that can be parsed and expanded by Behat Drupal extension.
   *
   * This is to be used in a @transform Behat hook or right into a step.
   *
   * TableNode example:
   *
   * | Title        | My node title       |
   * | Teaser       | My node teaser      |
   * | Date         | 2020-01-15T12:30:00 |
   *
   * Mapping array example:
   *
   * [
   *   'Title' => 'title',
   *   'Teaser' => 'field_teaser',
   *   'Date' => 'field_date',
   * ]
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   *   Table holding field / value pairs.
   * @param array $mapping
   *   List of Behat parsable field names, keyed by their human readable names.
   */
  protected function mapTableFields(TableNode $table, array $mapping): array {
    $fields = [];
    foreach ($table->getRowsHash() as $label => $value) {
      if (isset($mapping[$label])) {
        $fields[$mapping[$label]] = $value;
      }
    }
    return $fields;
  }

}
