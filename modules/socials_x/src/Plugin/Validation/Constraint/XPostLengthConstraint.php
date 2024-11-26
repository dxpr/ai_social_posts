<?php

namespace Drupal\socials_x\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates X post length.
 *
 * @Constraint(
 *   id = "XPostLength",
 *   label = @Translation("X Post Length"),
 * )
 */
class XPostLengthConstraint extends Constraint {
  public $message = 'X posts cannot be longer than 280 characters. Current length: %length characters.';
}