<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
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
   * When I fill in "Deadline date" with the date "29-08-2016 23:59".
   * When I fill in "Deadline date" with the time "23:59:00".
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @throws \Exception
   *    Thrown when more than one element match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value
   */
  public function fillInDateField(string $field_group, string $date_component, string $value): void {
    $field_selectors = $this->findDateFields($field_group);
    $field_selector = reset($field_selectors);
    $field = $field_selector->find('named', ['field', ucfirst($date_component)]);
    $this->fillDateField($field, $date_component, $value);
  }

  /**
   * Fills a date or time field at a datetime multivalue widget.
   *
   * When I fill in "Date" with the date "29-08-2016 23:59" at position 1.
   * When I fill in "Date" with the time "23:59:00" at position 1.
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   * @param int $position
   *   The multivalue field position starting from 1.
   *
   * @throws \Exception
   *    Thrown when more than one element match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value at position :position
   */
  public function fillInNthDateField(string $field_group, string $date_component, string $value, int $position): void {
    $field_selectors = $this->findDateFields($field_group);
    $field_selector = reset($field_selectors);
    $fields = $field_selector->findAll(
      'named',
      ['field', ucfirst($date_component)],
    );
    $this->fillDateField($fields[$position - 1], $date_component, $value);
  }

  /**
   * Fills a date or time field.
   */
  protected function fillDateField(NodeElement $field, string $date_component, string $value): void {
    // Ensure the date is in the format expected by the HTML.
    $value = $date_component === 'date' ? DrupalDateTime::createFromFormat('d-m-Y', $value)->format('Y-m-d') : $value;
    if ($this->getMink()->getSession()->getDriver() instanceof Selenium2Driver) {
      // Selenium setValue() clicks the date field before filling in, it
      // clicks the year segment and due to this the input value is incorrect.
      $field_id = $field->getAttribute('id');
      $script = "document.querySelector('input[id=$field_id]').value='$value';";
      $this->getMink()->getSession()->getDriver()->executeScript($script);
      return;
    }
    $field->setValue($value);
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
    $field_selectors = $this->findDateFields($field_group, 'Date range', '.field--widget-daterange-default');
    $field_selector = reset($field_selectors);

    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[class*="end-value-' . $date_component . '"]');
      $field_selector = reset($field_selector);
      $field = $field_selector->find(
        'named',
        ['field', ucfirst($date_component)],
      );
      $this->fillDateField($field, $date_component, $value);
      return;
    }

    $field = $field_selector->find(
      'named',
      ['field', ucfirst($date_component)],
    );
    $this->fillDateField($field, $date_component, $value);
  }

  /**
   * Fills a date or time field at a daterange_timezone widget.
   *
   * When I fill in "Start date" of "Event date" with the date
   * "29-08-2016 15:15" in the timezone Europe/Brussels".
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $value
   *   The value of the field.
   * @param string $timezone
   *   The timezone of date.
   *
   * @When I fill in :field_item of :field_group with the date :value in the timezone :timezone
   */
  public function fillDateRangeTimezoneField(string $field_item, string $field_group, string $value, string $timezone): void {
    $date = DrupalDateTime::createFromFormat('d-m-Y H:i', $value, $timezone);
    // Mapping for field items inside the date range field.
    $field_items = [
      'Start date' => 'value',
      'End date' => 'end-value',
    ];

    $field_selectors = $this->findDateFields($field_group, 'Date range', '.field--widget-daterange-timezone');
    $field_selector = reset($field_selectors);

    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[id*="' . $field_items[$field_item] . '"]');
      $field_selector = reset($field_selector);
    }

    foreach (['Date', 'Time'] as $date_component) {
      $value = $date_component === 'Time' ? $date->format('H:i:s') : $date->format('Y-m-d');
      if ($this->getMink()->getSession()->getDriver() instanceof Selenium2Driver) {
        // Selenium setValue() clicks the date field before filling in, it
        // clicks the year segment and due to this the input value is incorrect.
        $date_component = lcfirst($date_component);
        $script = "Array.from(document.querySelectorAll('legend span')).find(el => el.textContent === '$field_group').parentElement.parentElement.querySelector('.field--widget-daterange-timezone input[id*=\"$field_items[$field_item]-$date_component\"]').value='$value';";
        $this->getMink()->getSession()->getDriver()->executeScript($script);
        continue;
      }
      $field_selector->findField($date_component)->setValue($value);
    }

    // Fill in the timezone field of the date component.
    if ($field_item === 'Start date') {
      $field_selector = $field_selector->findAll('css', 'select[id*="-timezone"]');
      $field_selector = reset($field_selector);
      $field_selector->selectOption($timezone);
    }
  }

  /**
   * Set the date and time value of a datelist date range widget.
   *
   * When I set "22-02-2019 02:30" as the "Start date" of "My date field"
   * When I set "22-02-2019" as the "Start date" of "My date field"
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $value
   *   The value of the field.
   *
   * @When I set :value as the :field_item of :field_group
   */
  public function fillDateRangeSelectListField(string $field_item, string $field_group, string $value): void {
    $field_selectors = $this->findDateFields($field_group, 'Date range datelist', '.field--widget-daterange-datelist');

    $field_selector = reset($field_selectors);
    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[id*="end-value"]');
      $field_selector = reset($field_selector);
    }

    // Make sure that the step supports "Date only" and "Date and time" inputs.
    $date_components = [
      'Day' => 'd',
      'Month' => 'n',
      'Year' => 'Y',
    ];
    try {
      $date = DrupalDateTime::createFromFormat('d-m-Y', $value, 'UTC');
    }
    catch (\InvalidArgumentException $e) {
      $date_components += [
        'Hour' => 'G',
        'Minute' => 'i',
      ];
      $date = DrupalDateTime::createFromFormat('d-m-Y H:i', $value, 'UTC');
    }

    foreach ($date_components as $date_component => $date_component_format) {
      // For avoiding usage of minutes with leading zero sign,
      // we use casting to integer.
      $field_selector->selectFieldOption($date_component, (integer) $date->format($date_component_format));
    }
  }

  /**
   * Set the date and time value of a date list widget.
   *
   * When I set "Field" to the date "22-02-2019"
   * When I set "Field" to the date "22-02-2019 14:30" using format "d-m-Y H:i"
   *
   * @param string $field
   *   The label of the field.
   * @param string $value
   *   The value of the field.
   * @param string $format
   *   The dateformat of the field.
   *
   * @When I set :field to the date :value
   * @When I set :field to the date :value using format :format
   */
  public function fillDateSelectListField(string $field, string $value, string $format = 'd-m-Y'): void {
    $field_selectors = $this->findDateFields($field, 'Datetime list', '.field--widget-datetime-datelist');
    $field_selector = reset($field_selectors);

    $date = DrupalDateTime::createFromFormat($format, $value, 'UTC');
    $date_components = [
      'Year' => 'Y',
      'Month' => 'm',
      'Day' => 'd',
    ];
    if (strpos($format, 'H') !== FALSE) {
      $date_components['Hour'] = 'H';
    }
    if (strpos($format, 'i') !== FALSE) {
      $date_components['Minute'] = 'i';
    }
    foreach ($date_components as $date_component => $date_component_format) {
      // For avoiding usage of minutes with leading zero sign,
      // we use casting to integer.
      $field_selector->selectFieldOption($date_component, (integer) $date->format($date_component_format));
    }
  }

  /**
   * Finds a Date field.
   *
   * @param string $field
   *   The field name.
   * @param string $label
   *   The field label.
   * @param string $selector
   *   The field CSS selector.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   */
  protected function findDateFields(string $field, string $label = 'Date', string $selector = '.field--widget-datetime-default'): array {
    $field_selectors = $this->getSession()->getPage()->findAll('css', $selector);
    $field_selectors = array_filter($field_selectors, function (NodeElement $field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });

    if (empty($field_selectors)) {
      throw new \Exception(sprintf('%s field "%s" was not found.', $label, $field));
    }
    if (count($field_selectors) > 1) {
      throw new \Exception('More than one elements were found.');
    }
    return $field_selectors;
  }

}
