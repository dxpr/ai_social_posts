<?php

namespace Drupal\ai_social_posts;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a AiSocialPost entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup ai_social_posts
 */
interface AiSocialPostInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
