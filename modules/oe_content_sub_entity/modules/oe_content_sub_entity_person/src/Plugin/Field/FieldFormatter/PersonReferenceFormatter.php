<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity_person\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase;

/**
 * Plugin implementation of the Person reference fields formatter.
 *
 * @FieldFormatter(
 *   id = "oe_content_sub_entity_person_reference_formatter",
 *   label = @Translation("Persons reference formatter"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class PersonReferenceFormatter extends EntityReferenceRevisionsFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label_only' => NULL,
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
      '#title' => $this->t('Label only'),
      '#default_value' => $this->getSetting('label_only'),
    ];
    $elements['rel'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add rel="nofollow" to links'),
      '#return_value' => 'nofollow',
      '#default_value' => $this->getSetting('rel'),
    ];
    $elements['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open link in new window'),
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
      $summary[] = $this->t('Show label only as plain-text');
    }
    if (!empty($settings['rel'])) {
      $summary[] = $this->t('Add rel="@rel"', ['@rel' => $settings['rel']]);
    }
    if (!empty($settings['target'])) {
      $summary[] = $this->t('Open link in new window');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    $index = 0;
    $cacheable_metadata = new CacheableMetadata();
    /** @var \Drupal\oe_content_sub_entity_person\Entity\Person $entity */
    foreach ($this->getEntitiesToView($items, $langcode) as $entity) {
      $links = $entity->getEntitiesAsLinks();
      foreach ($links as $link) {
        if ($settings['label_only']) {
          $elements[$index] = [
            '#plain_text' => $link->getText(),
          ];
        }
        else {
          $url = $link->getUrl();
          $options = $url->getOptions();

          // Add optional 'rel' attribute to link options.
          if (!empty($settings['rel'])) {
            $options['attributes']['rel'] = $settings['rel'];
          }
          // Add optional 'target' attribute to link options.
          if (!empty($settings['target'])) {
            $options['attributes']['target'] = $settings['target'];
          }
          $url->setOptions($options);
          $elements[$index] = [
            '#type' => 'link',
            '#title' => $link->getText(),
            '#url' => $url,
            '#options' => $url->getOptions(),
          ];
        }

        $entity->getCacheableMetadata()->applyTo($elements[$index]);
        $cacheable_metadata->addCacheableDependency($entity->getCacheableMetadata());
        $index++;
      }
    }
    $cacheable_metadata->addCacheableDependency($items->getEntity());
    $cacheable_metadata->applyTo($elements);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getSetting('handler') === 'default:oe_person';
  }

}
