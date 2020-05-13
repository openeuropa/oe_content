<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_taxonomy_types\Entity\TaxonomyTypeAssociation;
use Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface;

/**
 * Taxonomy type association form.
 *
 * @property \Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface $entity
 */
class TaxonomyTypeAssociationForm extends EntityForm {

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
      '#description' => $this->t('Label for the taxonomy type association.'),
      '#required' => TRUE,
    ];

    $form['name'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->getName(),
      '#machine_name' => [
        'exists' => [$this, 'nameExists'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['fields'] = [
      '#type' => 'select',
      '#title' => $this->t('Fields'),
      '#description' => $this->t('Select the field target of this association.'),
      '#multiple' => TRUE,
      '#options' => $this->getAvailableFields(),
      '#default_value' => $entity->getFields(),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    ];

    $widgets = \Drupal::service('plugin.manager.oe_taxonomy_types.vocabulary_reference_widget')->getDefinitionsAsOptions();
    $form['widget_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Widget type'),
      '#options' => $widgets,
      '#default_value' => $entity->getWidgetType(),
      '#required' => TRUE,
    ];

    $form['taxonomy_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Taxonomy type'),
      '#options' => $this->getTaxonomyTypes(),
      '#default_value' => $entity->getTaxonomyType(),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    ];

    $form['predicate'] = [
      '#type' => 'select',
      '#title' => $this->t('Predicate'),
      '#options' => [
        'http://example.com/#contain' => $this->t('Contain'),
        'http://example.com/#about' => $this->t('About'),
      ],
      '#default_value' => $entity->getPredicate(),
      '#required' => TRUE,
    ];

    // @todo This should be done like core.
    $range = range(1, 10);
    $options = array_combine($range, $range);
    $options[TaxonomyTypeAssociationInterface::CARDINALITY_UNLIMITED] = $this->t('Unlimited');
    $form['cardinality'] = [
      '#type' => 'select',
      '#title' => $this->t('Allowed number of values'),
      '#options' =>  $options,
      '#default_value' => $entity->getCardinality() ?? TaxonomyTypeAssociationInterface::CARDINALITY_UNLIMITED,
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    ];

    $form['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required'),
      '#default_value' => $entity->isRequired(),
    ];

    $form['help_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Help text'),
      '#default_value' => $entity->getHelpText(),
      '#description' => $this->t('Help text to visualise under the field.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    // Save only the values of the fields, without keys.
    $fields = $form_state->getValue('fields', []);
    $form_state->setValue('fields', array_values($fields));

    return parent::buildEntity($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new taxonomy type association %label.', $message_args)
      : $this->t('Updated taxonomy type association %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

  protected function getTaxonomyTypes(): array {
    $storage = \Drupal::entityTypeManager()->getStorage('oe_taxonomy_type');
    $types = $storage->loadMultiple();

    return array_map(function ($type) {
      return $type->label();
    }, $types);
  }

  protected function getAvailableFields(): array {
    $storage = \Drupal::entityTypeManager()->getStorage('field_config');
    $query = $storage->getQuery();
    $query->condition('field_type', 'oe_taxonomy_type_field');
    $results = $query->execute();

    $fields = [];
    foreach ($storage->loadMultiple($results) as $field) {
      $label = $this->t('Field @field on entity @entity, bundle @bundle', [
        '@field' => $field->label(),
        '@entity' => $field->getTargetEntityTypeId(),
        '@bundle' => $field->getTargetBundle(),
      ]);
      $fields[$field->id()] = $label;
    }

    return $fields;
  }

  public function nameExists($value, array $element, FormStateInterface $form_state): bool {
    $storage = \Drupal::entityTypeManager()->getStorage('oe_taxonomy_type_association');
    $entity = $storage->load($this->entity->id());

    return isset($entity);
  }

}
