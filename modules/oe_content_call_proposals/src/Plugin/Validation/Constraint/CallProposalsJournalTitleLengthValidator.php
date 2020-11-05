<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Call For Proposals journal title length constraint.
 */
class CallProposalsJournalTitleLengthValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /* @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_call_proposals') {
      return;
    }

    $length = 128;

    $title = $node->oe_call_proposals_journal->title;
    if (!empty($title) && mb_strlen($title) > $length) {
      $this->context->buildViolation($constraint->errorMessage, ['@length' => $length])
        ->atPath('oe_call_proposals_journal')
        ->addViolation();
    }
  }

}
