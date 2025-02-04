<?php

namespace Drupal\ai_social_posts;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for managing social post types.
 */
class AiSocialPostTypeManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AiSocialPostTypeManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Gets all social post types.
   *
   * @return \Drupal\ai_social_posts\Entity\AiSocialPostTypeInterface[]
   *   An array of social post type entities.
   */
  public function getTypes() {
    return $this->entityTypeManager
      ->getStorage('ai_social_post_type')
      ->loadMultiple();
  }

}
