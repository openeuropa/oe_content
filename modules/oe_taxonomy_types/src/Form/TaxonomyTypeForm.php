<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

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
        'exists' => '\Drupal\oe_taxonomy_types\Entity\OeTaxonomyType::load',
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->get('description'),
      '#description' => $this->t('Description of the taxonomy type.'),
    ];

    $options = \Drupal::service('plugin.manager.oe_taxonomy_types.vocabulary_reference_handler')->getDefinitionsAsOptions();
    $form['vocabulary_type'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->t('Vocabulary type'),
      '#options' => $options,
      '#default_value' => $entity->isNew() ? 'skos_concept' : $entity->get('vocabulary_type'),
    ];

    return $form;
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

}
