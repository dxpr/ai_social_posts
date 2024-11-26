<?php

namespace Drupal\socials;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a SocialPost entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup socials
 */
interface SocialPostInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
