<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_document_reference\Form;

use Drupal\oe_content_entity\Form\CorporateEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for document reference entity edit forms.
 */
class DocumentReferenceForm extends CorporateEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Hide the label field.
    $form['name']['#access'] = FALSE;

    return $form;
  }

}
