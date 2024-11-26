<?php

namespace Drupal\socials;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for managing social post types.
 */
class SocialPostTypeManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new SocialPostTypeManager object.
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
   * @return \Drupal\socials\Entity\SocialPostTypeInterface[]
   *   An array of social post type entities.
   */
  public function getTypes() {
    return $this->entityTypeManager
      ->getStorage('social_post_type')
      ->loadMultiple();
  }

}