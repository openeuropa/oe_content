<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

/**
 * Provides a JS-based tabledrag row reordering method.
 *
 * This replaces Mink's dragTo() which is unreliable across different
 * Selenium driver versions (e.g. lullabot/mink-selenium2-driver 1.6 vs 1.7).
 */
trait TableDragTrait {

  /**
   * Moves a tabledrag row to a new position using JavaScript.
   *
   * @param string $tableSelector
   *   CSS selector for the tabledrag table.
   * @param int $sourceRow
   *   The 1-based index of the row to move.
   * @param int $targetRow
   *   The 1-based index of the row to place the source after.
   */
  protected function sortTableDragRow(string $tableSelector, int $sourceRow, int $targetRow): void {
    $this->getSession()->executeScript("
      var table = document.querySelector('$tableSelector');
      var rows = table.querySelectorAll('tbody > tr.draggable');
      var sourceIdx = $sourceRow - 1;
      var targetIdx = $targetRow - 1;
      var sourceEl = rows[sourceIdx];
      var targetEl = rows[targetIdx];
      if (sourceIdx < targetIdx) {
        targetEl.parentNode.insertBefore(sourceEl, targetEl.nextSibling);
      } else {
        targetEl.parentNode.insertBefore(sourceEl, targetEl);
      }
      var updatedRows = table.querySelectorAll('tbody > tr.draggable');
      var weight = 0;
      updatedRows.forEach(function(row) {
        var weightField = row.querySelector('.delta-order select, .delta-order input');
        if (weightField) {
          weightField.value = weight++;
          weightField.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    ");
  }

}
