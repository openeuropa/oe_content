/**
 * @file
 * JS code for collapsible actions button.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Process collapsible_actions elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches collapsibleActions behaviors.
   */
  Drupal.behaviors.collapsibleActions = {
    attach: function (context, settings) {
      var $actionsElement = $(once('collapsible-dropdown', '.collapsible-dropdown', context));
      // Attach event handlers to toggle button.
      $actionsElement.each(function () {
        var $this = $(this);
        var $toggle = $this.find('.collapsible-dropdown-toggle');

        $toggle.on('click', function (e) {
          e.preventDefault();
          $this.toggleClass('open');
        });

        $this.on('focusout', function (e) {
          setTimeout(function () {
            if ($this.has(document.activeElement).length == 0) {
              // The focus left the action button group, hide actions.
              $this.removeClass('open');
            }
          }, 1);
        });
      });
    }
  };

})(jQuery, Drupal, once);
