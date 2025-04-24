<?php

declare(strict_types=1);

namespace Drupal\oe_content\Element;

use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a render element for a collapsible actions.
 *
 * Collapsible actions can have two type of actions
 * - actions - these are default actions that are always visible.
 * - dropdown_actions - actions that are in dropdown sub component.
 *
 * Usage example:
 *
 * @code
 * $form['actions'] = [
 *   '#type' => 'collapsible_actions',
 *   'actions' => $actions,
 *   'dropdown_actions' => $dropdown_actions,
 * ];
 * @endcode
 */
#[FormElement('collapsible_actions')]
class CollapsibleActions extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#pre_render' => [
        [$class, 'preRenderCollapsibleActions'],
      ],
      '#theme' => 'collapsible_actions',
    ];
  }

  /**
   * Pre render callback for #type 'collapsible_actions'.
   *
   * @param array $element
   *   Element array of a #type 'collapsible_actions'.
   *
   * @return array
   *   The processed element.
   */
  public static function preRenderCollapsibleActions(array $element) {
    $element['#attached']['library'][] = 'oe_content/collapsible_widget';

    if (!empty($element['dropdown_actions'])) {
      foreach (Element::children($element['dropdown_actions']) as $key) {
        $dropdown_action = &$element['dropdown_actions'][$key];
        if (isset($dropdown_action['#ajax'])) {
          $dropdown_action = RenderElementBase::preRenderAjaxForm($dropdown_action);
        }
        if (empty($dropdown_action['#attributes'])) {
          $dropdown_action['#attributes'] = ['class' => ['collapsible-dropdown-action']];
        }
        else {
          $dropdown_action['#attributes']['class'][] = 'collapsible-dropdown-action';
        }
      }
    }

    return $element;
  }

}
