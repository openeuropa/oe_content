<?php

declare(strict_types = 1);

namespace Drupal\oe_content_social_media_field\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validation constraint for required fields of social media field.
 */
class SocialMediaFieldConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $values = $field->getValue();

    // We are not checking the type field.
    unset($values['type']);

    foreach ($values as $property => $value) {
      if (empty($value)) {
        $this->context->buildViolation($constraint->message, ['@name' => $field->getFieldDefinition()->getLabel()])
          ->atPath($property)
          ->addViolation();
      }
    }
  }

}
