<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\UtilityTrait;

/**
 * Defines date field step definitions.
 */
class DateFieldContext extends RawDrupalContext {

  use UtilityTrait;

  /**
   * Fills a date or time field at a datetime widget.
   *
   * Example: When I fill in "Start date" with the date "29-08-2016".
   * Example: When I fill in "Start date" with the time "06:59:00AM".
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @throws \Exception
   *    Thrown when more than one elements match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value
   */
  public function fillDateField(string $field_group, string $date_component, string $value): void {
    $field_selectors = $this->findDateFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }
    $field_selector = reset($field_selectors);
    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Fills a date or time field at a daterange widget.
   *
   * When I fill in "Start date" of "Event date" with the date "29-08-2016".
   * When I fill in "End date" of "Event date" with the time "06:59:00AM".
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @When I fill in :field_item of :field_group with the :date_component :value
   */
  public function fillDateRangeField(string $field_item, string $field_group, string $date_component, string $value): void {
    $field_selectors = $this->findDateRangeFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }
    $field_selector = reset($field_selectors);

    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[class*="end-value-' . $date_component . '"]');
      $field_selector = reset($field_selector);
    }

    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Select a date and time field at a daterange datelist widget.
   *
   * When I select date and time "29-08-2016 06:59"
   * from "Start date" of "Event date".
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $value
   *   The value of the field.
   *
   * @When I select date and time :value from :field_item of :field_group
   */
  public function fillDateRangeSelectListField(string $field_item, string $field_group, string $value): void {
    $field_selectors = $this->findDateListRangeFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }

    $field_selector = reset($field_selectors);
    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[id*="end-value"]');
      $field_selector = reset($field_selector);
    }

    $date = DrupalDateTime::createFromFormat('d-m-Y H:i', $value, 'UTC');
    foreach ([
      'Day' => 'd',
      'Month' => 'n',
      'Year' => 'Y',
      'Hour' => 'G',
      'Minute' => 'i',
    ] as $date_component => $date_component_format) {
      // For avoiding usage of minutes with leading zero sign,
      // we use casting to integer.
      $field_selector->selectFieldOption($date_component, (integer) $date->format($date_component_format));
    }
  }

  /**
   * Finds a datetime field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateFields(string $field): array {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-datetime-default');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date field {$field} was not found.");
    }
    return $field_selectors;
  }

  /**
   * Finds a daterange field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateRangeFields(string $field): array {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-daterange-default');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date range field {$field} was not found.");
    }
    return $field_selectors;
  }

  /**
   * Finds a daterange datelist field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateListRangeFields(string $field): array {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-daterange-datelist');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date range field {$field} was not found.");
    }
    return $field_selectors;
  }

}
