<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

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
  public function fillDateField($field_group, $date_component, $value) {
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
  public function fillDateRangeField($field_item, $field_group, $date_component, $value) {
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
  public function findDateFields($field) {
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
  public function findDateRangeFields($field) {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-daterange-default');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date range field {$field} was not found.");
    }
    return $field_selectors;
  }

}
