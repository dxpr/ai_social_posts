<?php

namespace Drupal\socials_x\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class XPostLengthConstraintValidator extends ConstraintValidator {
  public function validate($value, Constraint $constraint) {
    if (strlen($value->value) > 280) {
      $this->context->addViolation($constraint->message, ['%length' => strlen($value->value)]);
    }
  }
}