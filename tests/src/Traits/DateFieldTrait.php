<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides methods to fill in the date and time widgets.
 *
 * The widgets are located by the label of the field they belong to, so that
 * tests do not need to know the generated identifiers of each single date
 * or time input.
 */
trait DateFieldTrait {

  /**
   * Fills in a date or a time component of a datetime widget.
   *
   * @param string $field_group
   *   The label of the field the widget belongs to.
   * @param string $date_component
   *   The component to fill in, either "date" or "time".
   * @param string $value
   *   The value to fill in. Dates are expressed in the "d-m-Y" format.
   */
  protected function fillDateField(string $field_group, string $date_component, string $value): void {
    $wrapper = $this->findDateFieldWrapper($field_group);
    $field = $wrapper->find('named', ['field', ucfirst($date_component)]);
    $this->setDateFieldValue($field, $date_component, $value);
  }

  /**
   * Fills in a date or a time component of a multivalue datetime widget.
   *
   * @param string $field_group
   *   The label of the field the widget belongs to.
   * @param string $date_component
   *   The component to fill in, either "date" or "time".
   * @param string $value
   *   The value to fill in. Dates are expressed in the "d-m-Y" format.
   * @param int $position
   *   The delta of the value to fill in, starting from 1.
   */
  protected function fillNthDateField(string $field_group, string $date_component, string $value, int $position): void {
    $wrapper = $this->findDateFieldWrapper($field_group);
    $fields = $wrapper->findAll('named', ['field', ucfirst($date_component)]);
    $this->setDateFieldValue($fields[$position - 1], $date_component, $value);
  }

  /**
   * Fills in a date or a time component of a daterange widget.
   *
   * @param string $field_item
   *   The item of the widget to fill in, either "Start date" or "End date".
   * @param string $field_group
   *   The label of the field the widget belongs to.
   * @param string $date_component
   *   The component to fill in, either "date" or "time".
   * @param string $value
   *   The value to fill in. Dates are expressed in the "d-m-Y" format.
   */
  protected function fillDateRangeField(string $field_item, string $field_group, string $date_component, string $value): void {
    $wrapper = $this->findDateFieldWrapper($field_group, 'Date range', '.field--widget-daterange-default');

    if ($field_item === 'End date') {
      $wrapper = $wrapper->find('css', 'div[class*="end-value-' . $date_component . '"]');
    }

    $field = $wrapper->find('named', ['field', ucfirst($date_component)]);
    $this->setDateFieldValue($field, $date_component, $value);
  }

  /**
   * Fills in an item of a daterange widget which carries a timezone.
   *
   * Both the date and the time components are filled in, and the timezone
   * select is set when filling in the start date.
   *
   * @param string $field_item
   *   The item of the widget to fill in, either "Start date" or "End date".
   * @param string $field_group
   *   The label of the field the widget belongs to.
   * @param string $value
   *   The value to fill in, expressed in the "d-m-Y H:i" format.
   * @param string $timezone
   *   The timezone the value is expressed in.
   */
  protected function fillDateRangeTimezoneField(string $field_item, string $field_group, string $value, string $timezone): void {
    $date = DrupalDateTime::createFromFormat('d-m-Y H:i', $value, $timezone);
    // Maps the items of the widget to the identifier part they use.
    $field_items = [
      'Start date' => 'value',
      'End date' => 'end-value',
    ];

    $wrapper = $this->findDateFieldWrapper($field_group, 'Date range', '.field--widget-daterange-timezone');
    if ($field_item === 'End date') {
      $wrapper = $wrapper->find('css', 'div[id*="' . $field_items[$field_item] . '"]');
    }

    foreach (['Date', 'Time'] as $date_component) {
      $component_value = $date_component === 'Time' ? $date->format('H:i:s') : $date->format('Y-m-d');
      if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
        // Selenium clicks the field before filling it in, which lands on the
        // year segment and results in an incorrect value. Set it directly.
        $component = lcfirst($date_component);
        $script = "Array.from(document.querySelectorAll('legend span')).find(el => el.textContent === '$field_group').parentElement.parentElement.querySelector('.field--widget-daterange-timezone input[id*=\"{$field_items[$field_item]}-$component\"]').value='$component_value';";
        $this->getSession()->getDriver()->executeScript($script);
        continue;
      }
      $wrapper->findField($date_component)->setValue($component_value);
    }

    // The timezone select is shared by both items of the widget.
    if ($field_item === 'Start date') {
      $wrapper->find('css', 'select[id*="-timezone"]')->selectOption($timezone);
    }
  }

  /**
   * Sets the value of a single date or time input.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The input element.
   * @param string $date_component
   *   The component the input holds, either "date" or "time".
   * @param string $value
   *   The value to set. Dates are expressed in the "d-m-Y" format.
   */
  protected function setDateFieldValue(NodeElement $field, string $date_component, string $value): void {
    // Ensure the date is in the format expected by the HTML input.
    $value = $date_component === 'date' ? DrupalDateTime::createFromFormat('d-m-Y', $value)->format('Y-m-d') : $value;

    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      // Selenium clicks the field before filling it in, which lands on the
      // year segment and results in an incorrect value. Set it directly.
      $field_id = $field->getAttribute('id');
      $this->getSession()->getDriver()->executeScript("document.querySelector('input[id=$field_id]').value='$value';");
      return;
    }

    $field->setValue($value);
  }

  /**
   * Returns the wrapper of the date widget belonging to the given field.
   *
   * @param string $field
   *   The label of the field.
   * @param string $label
   *   The human readable name of the widget type, used in the exceptions.
   * @param string $selector
   *   The CSS selector of the widget wrapper.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The widget wrapper element.
   */
  protected function findDateFieldWrapper(string $field, string $label = 'Date', string $selector = '.field--widget-datetime-default'): NodeElement {
    $wrappers = $this->getSession()->getPage()->findAll('css', $selector);
    $wrappers = array_filter($wrappers, function (NodeElement $wrapper) use ($field) {
      return $wrapper->has('named', ['content', $field]);
    });

    if (empty($wrappers)) {
      throw new \InvalidArgumentException(sprintf('%s field "%s" was not found.', $label, $field));
    }
    if (count($wrappers) > 1) {
      throw new \InvalidArgumentException(sprintf('More than one %s field "%s" was found.', strtolower($label), $field));
    }

    return reset($wrappers);
  }

}
