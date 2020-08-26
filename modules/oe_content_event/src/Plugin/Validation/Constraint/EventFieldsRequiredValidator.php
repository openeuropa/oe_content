<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Checks if the event fields are provided if required.
 */
class EventFieldsRequiredValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /* @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_event') {
      return;
    }

    $this->validateOrganiserGroupFields($constraint, $node);
    $this->validateRegistrationGroupFields($constraint, $node);

    $online_required_fields = [
      'oe_event_online_type',
      'oe_event_online_dates',
      'oe_event_online_link',
    ];
    // Check if any of these "Online" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($online_required_fields, $constraint, $node);

    $description_fields_required = [
      'oe_event_description_summary',
      'oe_event_featured_media',
      'oe_event_featured_media_legend',
      'body',
    ];
    // Check if any of these "Description" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($description_fields_required, $constraint, $node);
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
   * Validate organiser information consistency.
   *
   * An organiser can either be a custom string or a reference to a corporate
   * vocabulary, depending from the value of `oe_event_organiser_is_internal`.
   *
   * This tests that, if one is set, the other is always not, depending
   * whether the organiser is marked as internal or not.
   *
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint object.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   */
  protected function validateOrganiserGroupFields(Constraint $constraint, NodeInterface $node) {
    if ($node->get('oe_event_organiser_internal')->isEmpty() && $node->get('oe_event_organiser_name')->isEmpty()) {
      return;
    }

    $violation = NULL;
    $oe_event_organiser_is_internal_value = (bool) $node->get('oe_event_organiser_is_internal')->value;

    if (!$oe_event_organiser_is_internal_value && !$node->get('oe_event_organiser_internal')->isEmpty()) {
      // If checkbox "Organiser is internal" isn't checked,
      // field "Internal organiser" has to be empty.
      $violation = $this->context->buildViolation('When @is_internal field is not checked, @internal field have to be empty.', [
        '@is_internal' => $node->getFieldDefinition('oe_event_organiser_is_internal')->getLabel(),
        '@internal' => $node->getFieldDefinition('oe_event_organiser_internal')->getLabel(),
      ]);
    }

    if ($oe_event_organiser_is_internal_value && !$node->get('oe_event_organiser_name')->isEmpty()) {
      // If checkbox "Organiser is internal" is checked,
      // field "Organiser name" has to be empty.
      $violation = $this->context->buildViolation('When @is_internal field is checked, @organiser_name field has to be empty.', [
        '@is_internal' => $node->getFieldDefinition('oe_event_organiser_is_internal')->getLabel(),
        '@organiser_name' => $node->getFieldDefinition('oe_event_organiser_name')->getLabel(),
      ]);
    }

    if ($violation instanceof ConstraintViolationBuilderInterface) {
      // Highlight both fields because checkbox "Organiser is internal" can not
      // be highlighted and field with error is hidden.
      (clone $violation)
        ->atPath('oe_event_organiser_name')
        ->addViolation();
      $violation
        ->atPath('oe_event_organiser_internal')
        ->addViolation();
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
    $violation = NULL;
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
