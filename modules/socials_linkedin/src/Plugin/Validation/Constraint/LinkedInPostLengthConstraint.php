<?php

namespace Drupal\socials_linkedin\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates LinkedIn post length.
 *
 * @Constraint(
 *   id = "LinkedInPostLength",
 *   label = @Translation("LinkedIn Post Length"),
 * )
 */
class LinkedInPostLengthConstraint extends Constraint {
  public $message = 'LinkedIn posts cannot be longer than 3000 characters. Current length: %length characters.';
}