<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_taxonomy_types\Entity\TaxonomyType;

/**
 * Taxonomy type form.
 */
class TaxonomyTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t('Label for the taxonomy type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [TaxonomyType::class, 'load'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->get('description'),
      '#description' => $this->t('Description of the taxonomy type.'),
    ];

    $handler_id = $entity->isNew() ? NULL : $entity->get('handler');
    $handler_id = $form_state->getValue('handler', $handler_id);

    /** @var \Drupal\oe_taxonomy_types\VocabularyReferenceHandlerPluginManager $vocabulary_reference_manager */
    $vocabulary_reference_manager = \Drupal::service('plugin.manager.oe_taxonomy_types.vocabulary_reference_handler');
    $form['handler'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->t('Vocabulary type'),
      '#options' => $vocabulary_reference_manager->getDefinitionsAsOptions(),
      '#default_value' => $handler_id,
      '#ajax' => [
        'callback' => [$this, 'updateHandlerSettings'],
        'wrapper' => 'taxonomy-handler-settings-wrapper',
      ],
    ];

    $form['handler_settings'] = [
      '#tree' => TRUE,
      '#type' => 'container',
      '#id' => 'taxonomy-handler-settings-wrapper',
      //'#process' => [[get_class($this), 'processAjaxSettings']],
      '#process' => [[EntityReferenceItem::class, 'fieldSettingsAjaxProcess']],
      '#element_validate' => [[$this, 'validateSelectionPluginHandlerConfiguration']],
      '#parents' => ['settings', 'handler_settings'],
    ];

    if ($handler_id) {
      /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface $handler */
      $plugin = $vocabulary_reference_manager->createInstance($handler_id);
      $form['handler_settings'] += $plugin->getHandler($entity->get('handler_settings'))->buildConfigurationForm([], $form_state);

      // Handle this in a wrapper plugin maybe?
      $form['handler_settings']['auto_create']['#access'] = FALSE;
      $form['handler_settings']['auto_create']['#value'] = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    // The selection handlers expect the form elements to be under a specific
    // array key. Move it up to our entity property.
    $handler_settings = $form_state->getValue(['settings', 'handler_settings'], []);
    $form_state->setValue('handler_settings', $handler_settings);

    return parent::buildEntity($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new taxonomy type %label.', $message_args)
      : $this->t('Updated taxonomy type %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

  /**
   * Ajax callback to update the handler settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateHandlerSettings(array &$form, FormStateInterface $form_state): array {
    return $form['handler_settings'];
  }

  /**
   * Form element validation handler; Invokes selection plugin's validation.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the (entire) configuration form.
   */
  public function validateSelectionPluginHandlerConfiguration(array $form, FormStateInterface $form_state): void {
    $handler = $form_state->getValue('handler', NULL);
    if ($handler === NULL) {
      return;
    }

    $plugin = \Drupal::service('plugin.manager.oe_taxonomy_types.vocabulary_reference_handler')->createInstance($handler);
    $plugin->getHandler()->validateConfigurationForm($form, $form_state);
  }

}
