<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_test_constraint\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates required fields for Timeline paragraph.
 */
class TestConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * TestConstraintValidator constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if ($value->isEmpty()) {
      return;
    }

    $parameters = [
      '%label' => 'a label',
      '%title' => 'a title',
      '%body' => 'a body',
    ];
    $error_paths = $this->state->get('oe_content_timeline_test_constraint.error_paths', []);

    foreach ($value as $delta => $item) {
      if (!isset($error_paths[$delta])) {
        continue;
      }

      $this->context->buildViolation($constraint->message)
        ->atPath($error_paths[$delta])
        ->setParameters($parameters)
        ->addViolation();
    }
  }

}
