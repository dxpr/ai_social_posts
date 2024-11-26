<?php

namespace Drupal\socials_x;

use Drupal\socials\SocialPostValidatorInterface;

/**
 * Validates X posts.
 */
class XPostValidator implements SocialPostValidatorInterface {

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
    return 280;
  }

}