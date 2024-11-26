<?php

namespace Drupal\socials_linkedin;

use Drupal\socials\SocialPostValidatorInterface;

/**
 * Validates LinkedIn posts.
 */
class LinkedInPostValidator implements SocialPostValidatorInterface {

  /**
   * {@inheritdoc}
   */
  public function validateLength($text): bool {
    return strlen($text) <= $this->getMaxLength();
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxLength(): int {
    return 3000;
  }

}