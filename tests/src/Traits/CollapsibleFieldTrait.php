<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;

/**
 * Test trait for collapsible JS tests.
 */
trait CollapsibleFieldTrait {

  /**
   * Assert the content of the widget.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The field element.
   * @param array $expected_rows
   *   The values to check, keyed by 'mode' (open/closed), and 'values'.
   */
  protected function assertCollapsibleTable(NodeElement $field, array $expected_rows) {
    $table = $field->find('css', 'table');
    $rows = $table->findAll('css', 'tbody tr');
    $this->assertCount(count($expected_rows), $rows);
    foreach ($expected_rows as $index => $values) {
      foreach ($values as $field_name => $value) {
        if ($field_name === 'mode') {
          continue;
        }
        if ($values['mode'] === 'open') {
          $field = $rows[$index]->findField($field_name);
          $this->assertSession()->elementNotExists('css', '.collapsible-summary', $rows[$index]);
          $this->assertNotNull($field, "Field $field_name not found in row $index.");
          $this->assertEquals($value, $field->getValue());
        }
        elseif ($values['mode'] === 'closed') {
          $this->assertSession()->elementExists('css', '.collapsible-summary', $rows[$index]);
          $this->assertStringContainsString($value, $rows[$index]->getText());
        }
      }
    }
  }

  /**
   * Fill a row in the collapsible widget.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The field element.
   * @param int $row
   *   The row number.
   * @param array $values
   *   The values to fill.
   */
  protected function fillCollapsibleRow(NodeElement $field, int $row, array $values): void {
    // Find nth row by css selector.
    $row = $field->find('css', "tbody tr:nth-child($row)");
    foreach ($values as $field_name => $value) {
      $row->fillField($field_name, $value);
    }
  }

  /**
   * Perform an action on a row in the collapsible widget.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The field element.
   * @param int $row
   *   The row number.
   * @param string $operation
   *   The operation to perform.
   */
  protected function performCollapsibleAction(NodeElement $field, int $row, string $operation): void {
    $actions = $field->find('css', "tbody tr:nth-child($row) .collapsible-actions");
    $actions->pressButton($operation);
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Perform a subaction on a row in the collapsible widget.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The field element.
   * @param int $row
   *   The row number.
   * @param string $operation
   *   The operation to perform.
   */
  protected function performCollapsibleSubaction(NodeElement $field, int $row, string $operation): void {
    $dropdown = $field->find('css', "tbody tr:nth-child($row) .collapsible-dropdown");
    $dropdown->click();
    $dropdown->pressButton($operation);
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Perform a subaction on a header.
   *
   * @param \Behat\Mink\Element\NodeElement $field
   *   The field element.
   * @param string $operation
   *   The operation to perform.
   */
  protected function performCollapsibleHeaderSubaction(NodeElement $field, string $operation): void {
    $dropdown = $field->find('css', "thead tr:first-child .collapsible-dropdown");
    $dropdown->click();
    $dropdown->pressButton($operation);
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

}
