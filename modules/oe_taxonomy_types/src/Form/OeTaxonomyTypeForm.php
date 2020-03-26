<?php

namespace Drupal\oe_taxonomy_types\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Taxonomy type form.
 *
 * @property \Drupal\oe_taxonomy_types\OeTaxonomyTypeInterface $entity
 */
class OeTaxonomyTypeForm extends EntityForm {

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

    $form['vocabulary_type'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->t('Vocabulary type'),
      // @todo this should retrieve possible entity types automatically, maybe.
      '#options' => [
        'skos_concept' => $this->t('Corporate vocabulary'),
        'taxonomy_vocabulary' => $this->t('Local vocabulary'),
      ],
      '#default_value' => $entity->isNew() ? 'skos_concept' : $entity->get('vocabulary_type'),
    ];

    // @todo missing extra settings for voc type.

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
