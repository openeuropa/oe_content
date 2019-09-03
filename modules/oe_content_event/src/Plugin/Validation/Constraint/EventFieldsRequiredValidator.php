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

    $online_required_fields = [
      'field_online_type',
      'field_online_time_start',
      'field_online_link',
    ];
    // Check if any of these "Online" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($online_required_fields, $constraint, $node);

    $description_fields_required = [
      'field_featured_media',
      'field_featured_media_legend',
      'body',
    ];
    // Check if any of these "Description" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($description_fields_required, $constraint, $node);

    $registration_fields_required = [
      'field_registration_url',
      'field_registration_status',
      'field_registration_start_date',
      'field_registration_end_date',
    ];
    // Check if any of these "Registration" field group fields are filled in,
    // then they are all required.
    $this->validateGroupFieldsEmpty($registration_fields_required, $constraint, $node);
  }

  /**
   * Helper function to provide violation on a set of fields that are required.
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
   * Helper function to provide violation on a set of "Organiser" fields.
   *
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint object.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   */
  protected function validateOrganiserGroupFields(Constraint $constraint, NodeInterface $node) {
    $violation = NULL;
    $fields_state = ($node->get('field_organiser_internal')->isEmpty() + $node->get('field_organiser_name')->isEmpty());
    // Both fields are empty.
    if ($fields_state === 2) {
      $violation = $this->context->buildViolation('You have to fill in at least one of the following fields: @internal or @organiser_name', [
        '@internal' => $node->getFieldDefinition('field_organiser_internal')->getLabel(),
        '@organiser_name' => $node->getFieldDefinition('field_organiser_name')->getLabel(),
      ]);
    }
    // Both fields are filled in.
    elseif ($fields_state === 0) {
      $violation = $this->context->buildViolation('You have to fill in only one of the following fields @internal or @organiser_name, not both', [
        '@internal' => $node->getFieldDefinition('field_organiser_internal')->getLabel(),
        '@organiser_name' => $node->getFieldDefinition('field_organiser_name')->getLabel(),
      ]);
    }

    if ($violation instanceof ConstraintViolationBuilderInterface) {
      // Highlight empty 'Organiser name' field.
      (clone $violation)
        ->atPath('field_organiser_name')
        ->addViolation();

      // Highlight empty 'Internal organiser' field.
      $violation
        ->atPath('field_organiser_internal')
        ->addViolation();
    }
  }

}
