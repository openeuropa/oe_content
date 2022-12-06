<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Checks if the event fields are provided if required.
 */
class EventFieldsRequiredValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /** @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_event') {
      return;
    }

    $this->validateRegistrationGroupFields($constraint, $node);

    $online_required_fields = [
      'oe_event_online_type',
      'oe_event_online_dates',
      'oe_event_online_link',
    ];
    // Check if any of these "Online" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($online_required_fields, $constraint, $node);
  }

  /**
   * Validate that if one field is not empty then all the rest are required too.
   *
   * @param array $fields
   *   List of fields to check.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint object.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   */
  protected function validateGroupFieldsEmpty(array $fields, Constraint $constraint, NodeInterface $node) {
    $field_values = [];

    // Check for values in each field.
    foreach ($fields as $field_name) {
      $field_values[$field_name] = $node->get($field_name)->isEmpty();
    }

    // If any of these fields are NOT empty, then all the rest are required.
    if (in_array(FALSE, $field_values)) {
      foreach ($field_values as $field_name => $has_value) {
        if ($has_value) {
          $this->context->buildViolation($constraint->message, ['@name' => $node->getFieldDefinition($field_name)->getLabel()])
            ->atPath($field_name)
            ->addViolation();
        }
      }
    }
  }

  /**
   * Validate event registration consistency.
   *
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint object.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   */
  protected function validateRegistrationGroupFields(Constraint $constraint, NodeInterface $node) {
    $field_values = [];
    $required_field = 'oe_event_registration_url';
    $fields_to_check = [
      'oe_event_entrance_fee',
      'oe_event_registration_dates',
      'oe_event_registration_capacity',
    ];

    // Check for values in each field.
    foreach ($fields_to_check as $field_name) {
      $field_values[$field_name] = $node->get($field_name)->isEmpty();
    }

    // If any of these fields are NOT empty, then the required field
    // must be filled in.
    if (in_array(FALSE, $field_values) && $node->get($required_field)->isEmpty()) {
      $this->context->buildViolation($constraint->message, ['@name' => $node->getFieldDefinition($required_field)->getLabel()])
        ->atPath($required_field)
        ->addViolation();
    }
  }

}
