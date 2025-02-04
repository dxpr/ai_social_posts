<?php

namespace Drupal\ai_social_posts\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\ai_social_posts\AiSocialPostTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all social post bundles.
 */
class AiSocialPostLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The social post type manager.
   *
   * @var \Drupal\ai_social_posts\AiSocialPostTypeManager
   */
  protected $postTypeManager;

  /**
   * Constructs a new AiSocialPostLocalTasks.
   *
   * @param \Drupal\ai_social_posts\AiSocialPostTypeManager $post_type_manager
   *   The social post type manager.
   */
  public function __construct(AiSocialPostTypeManager $post_type_manager) {
    $this->postTypeManager = $post_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('ai_social_posts.post_type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $types = $this->postTypeManager->getTypes();

    // Only create derivatives if we have multiple types.
    if (count($types) <= 1) {
      return [];
    }

    foreach ($types as $type) {
      $this->derivatives[$type->id()] = array_merge($base_plugin_definition, [
        'title' => $type->label(),
        'route_name' => "ai_social_posts.node.{$type->id()}_posts",
        'parent_id' => 'ai_social_posts.node.ai_social_posts',
      ]);
    }

    return $this->derivatives;
  }

}
