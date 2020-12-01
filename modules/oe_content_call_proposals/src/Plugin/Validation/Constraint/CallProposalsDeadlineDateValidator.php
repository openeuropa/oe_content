<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals\Plugin\Validation\Constraint;

use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapperInterface;

/**
 * Validates the Call For Proposals Deadline date constraint.
 */
class CallProposalsDeadlineDateValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /** @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_call_proposals') {
      return;
    }

    $wrapper = CallForProposalsNodeWrapper::getInstance($node);

    $deadline_model = $wrapper->getModel();
    $has_deadline_date = $wrapper->hasDeadlineDate();
    if ($deadline_model !== CallForProposalsNodeWrapperInterface::MODEL_PERMANENT && $has_deadline_date === FALSE) {
      $this->context->buildViolation($constraint->errorMessage, ['@model' => $wrapper->getModelLabel()])
        ->atPath('oe_call_proposals_deadline')
        ->addViolation();
    }
  }

}
