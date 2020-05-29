<?php

declare(strict_types = 1);

namespace Drupal\oe_timeline_test_constraint\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates required fields for Timeline paragraph.
 */
class TestConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if ($value->isEmpty()) {
      return;
    }

    $parameters = [
      '%label' => 'label',
      '%title' => 'title',
      '%body' => 'body',
    ];
    foreach ($value as $delta => $item) {
      /** @var \Drupal\oe_content_timeline_field\Plugin\Field\FieldType\TimelineFieldItem $item */
      $value = $item->getValue();
      if ($value['label'] === '' || $value['label'] === NULL) {
        if ($value['title'] === '' || $value['title'] === NULL) {
          if ($value['body'] !== '' || $value['body'] !== NULL) {
            $this->context->buildViolation($constraint->message)
              ->atPath($delta)
              ->setParameters($parameters)
              ->addViolation();
          }
        }
      }
    }
  }

}
