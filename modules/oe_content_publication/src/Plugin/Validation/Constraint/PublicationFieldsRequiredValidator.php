<?php

declare(strict_types = 1);

namespace Drupal\oe_content_publication\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks if the publication fields are provided if required.
 */
class PublicationFieldsRequiredValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /** @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_publication') {
      return;
    }

    $is_collection = (bool) $node->get('oe_publication_collection')->value;

    if ($is_collection && $node->get('oe_publication_publications')->isEmpty()) {
      $this->context->buildViolation($constraint->message, ['@name' => $node->getFieldDefinition('oe_publication_publications')->getLabel()])
        ->atPath('oe_publication_publications')
        ->addViolation();
    }

    if (!$is_collection && $node->get('oe_documents')->isEmpty()) {
      $this->context->buildViolation($constraint->message, ['@name' => $node->getFieldDefinition('oe_documents')->getLabel()])
        ->atPath('oe_documents')
        ->addViolation();
    }
  }

}
