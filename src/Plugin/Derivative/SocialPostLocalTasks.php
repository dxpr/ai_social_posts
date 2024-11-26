<?php

namespace Drupal\socials\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\socials\SocialPostTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all social post bundles.
 */
class SocialPostLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The social post type manager.
   *
   * @var \Drupal\socials\SocialPostTypeManager
   */
  protected $postTypeManager;

  /**
   * Constructs a new SocialPostLocalTasks.
   *
   * @param \Drupal\socials\SocialPostTypeManager $post_type_manager
   *   The social post type manager.
   */
  public function __construct(SocialPostTypeManager $post_type_manager) {
    $this->postTypeManager = $post_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('socials.post_type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->postTypeManager->getTypes() as $type) {
      $this->derivatives[$type->id()] = $base_plugin_definition;
      $this->derivatives[$type->id()]['title'] = $type->label();
      $this->derivatives[$type->id()]['route_name'] = "socials.node.{$type->id()}_posts";
      $this->derivatives[$type->id()]['parent_id'] = 'socials.node.social_posts';
      $this->derivatives[$type->id()]['route_parameters'] = [
        'bundle' => $type->id(),
      ];
    }

    return $this->derivatives;
  }

}
