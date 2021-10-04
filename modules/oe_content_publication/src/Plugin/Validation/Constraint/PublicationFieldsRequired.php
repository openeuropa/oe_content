<?php

declare(strict_types = 1);

namespace Drupal\oe_content_publication\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the publication fields are provided if required.
 *
 * @Constraint(
 *   id = "PublicationFieldsRequired",
 *   label = @Translation("Publication fields required", context = "Validation")
 * )
 */
class PublicationFieldsRequired extends Constraint {

  /**
   * Violation message. Use the same message as FormValidator.
   *
   * Note that the name argument is not sanitized so that translators only have
   * one string to translate. The name is sanitized in self::validate().
   *
   * @var string
   */
  public $message = '@name field is required.';

}
