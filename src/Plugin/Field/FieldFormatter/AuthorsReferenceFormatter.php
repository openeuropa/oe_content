<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the Author reference fields formatter.
 *
 * @FieldFormatter(
 *   id = "authors_reference_formatter",
 *   label = @Translation("Authors reference formatter"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class AuthorsReferenceFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label_only' => '',
      'rel' => '',
      'target' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['label_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Label only'),
      '#default_value' => $this->getSetting('label_only'),
    ];
    $elements['rel'] = [
      '#type' => 'checkbox',
      '#title' => t('Add rel="nofollow" to links'),
      '#return_value' => 'nofollow',
      '#default_value' => $this->getSetting('rel'),
    ];
    $elements['target'] = [
      '#type' => 'checkbox',
      '#title' => t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $this->getSetting('target'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $settings = $this->getSettings();

    if (!empty($settings['label_only'])) {
      $summary[] = t('Show label only as plain-text');
    }
    if (!empty($settings['rel'])) {
      $summary[] = t('Add rel="@rel"', ['@rel' => $settings['rel']]);
    }
    if (!empty($settings['target'])) {
      $summary[] = t('Open link in new window');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    /** @var \Drupal\oe_content\Entity\Author $entity */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $element[$delta] = $entity->getAuthorsAsLinks();
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getSetting('handler') === 'default:oe_author';
  }

}
