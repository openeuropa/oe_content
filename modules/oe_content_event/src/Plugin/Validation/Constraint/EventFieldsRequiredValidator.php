<?php

namespace Drupal\oe_content_event\Plugin\Validation\Constraint;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Checks if the event fields are provided if required.
 */
class EventFieldsRequiredValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /* @var \Drupal\node\NodeInterface $items */
    if (!isset($items) || $items->getType() !== 'oe_event') {
      return;
    }

    // Check for the "Online" fields to validate.
    // The "Online time start" and "Online link" fields are NOT required if
    // the "Online type" is not provided.
    $required = !empty($items->field_online_type->getValue());
    if ($required && empty($items->field_online_time_start->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_online_time_start')->getLabel()])
        ->atPath('field_online_time_start')
        ->addViolation();
    }
    if ($required && empty($items->field_online_link->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_online_link')->getLabel()])
        ->atPath('field_online_link')
        ->addViolation();
    }

    // Check for the "Description" fields to validate.
    // The "Featured media legend" and the "Body" are NOT required if the
    // "Featured media" is not provided.
    $required = !empty($items->field_featured_media->getValue());
    if ($required && empty($items->field_featured_media_legend->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_featured_media_legend')->getLabel()])
        ->atPath('field_featured_media_legend')
        ->addViolation();
    }
    if ($required && empty($items->body->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('body')->getLabel()])
        ->atPath('body')
        ->addViolation();
    }

    // Check for the "Registration" fields to validate.
    // The "Registration status", "Registration start date" and the
    // "Registration end date" are NOT required if the "Registration URL"
    // is not provided.
    $required = !empty($items->field_registration_url->getValue());
    if ($required && empty($items->field_registration_status->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_registration_status')->getLabel()])
        ->atPath('field_registration_status')
        ->addViolation();
    }
    if ($required && empty($items->field_registration_start_date->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_registration_start_date')->getLabel()])
        ->atPath('field_registration_start_date')
        ->addViolation();
    }
    if ($required && empty($items->field_registration_end_date->getValue())) {
      $this->context->buildViolation($constraint->message, ['@name' => $items->getFieldDefinition('field_registration_end_date')->getLabel()])
        ->atPath('field_registration_end_date')
        ->addViolation();
    }
  }

}
