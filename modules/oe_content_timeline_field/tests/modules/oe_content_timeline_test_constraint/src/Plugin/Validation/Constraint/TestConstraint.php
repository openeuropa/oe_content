<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_test_constraint\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Test constraint for Timeline widget element.
 *
 * @Constraint(
 *   id = "oe_content_timeline_test_constraint",
 *   label = @Translation("Test constraint", context = "Validation"),
 *   type = "string"
 * )
 */
class TestConstraint extends Constraint {

  /**
   * The error message.
   *
   * @var string
   */
  public $message = '%label and %title fields cannot be empty when %body is specified.';

}
