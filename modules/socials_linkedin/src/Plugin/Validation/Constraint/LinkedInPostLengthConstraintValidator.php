<?php

namespace Drupal\socials_linkedin\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LinkedInPostLengthConstraintValidator extends ConstraintValidator {
  public function validate($value, Constraint $constraint) {
    if (strlen($value->value) > 3000) {
      $this->context->addViolation($constraint->message, ['%length' => strlen($value->value)]);
    }
  }
}