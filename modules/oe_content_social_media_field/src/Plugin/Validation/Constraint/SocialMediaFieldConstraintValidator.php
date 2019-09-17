<?php

declare(strict_types = 1);

namespace Drupal\oe_content_social_media_field\Plugin\Validation\Constraint;

use Drupal\oe_content_social_media_field\Plugin\Field\FieldType\SocialMediaLinkItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validation handler for required fields of social media field.
 *
 * We need to make sure that URL or Title does not get saved without
 * one another.
 */
class SocialMediaFieldConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    if (!$field instanceof SocialMediaLinkItem) {
      return;
    }

    $values = $field->getValue();
    if (empty($values)) {
      return;
    }

    // We are not checking the type field.
    if (isset($values['type'])) {
      unset($values['type']);
    }
    foreach ($values as $property => $property_value) {
      if (empty($property_value)) {
        $this->context->buildViolation($constraint->message, ['@name' => $field->getFieldDefinition()->getLabel()])
          ->atPath($property)
          ->addViolation();
      }
    }
  }

}
