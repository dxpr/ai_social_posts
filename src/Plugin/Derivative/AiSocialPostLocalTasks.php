<?php

namespace Drupal\ai_social_posts\Plugin\Derivative;

use Drupal\ai_social_posts\AiSocialPostTypeManager;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
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
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Constructs a new AiSocialPostLocalTasks.
   *
   * @param \Drupal\ai_social_posts\AiSocialPostTypeManager $post_type_manager
   *   The social post type manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   */
  public function __construct(AiSocialPostTypeManager $post_type_manager, RouteProviderInterface $route_provider) {
    $this->postTypeManager = $post_type_manager;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('ai_social_posts.post_type_manager'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $types = $this->postTypeManager->getTypes();

    // Create derivatives for all types, but only if the route exists.
    foreach ($types as $type) {
      $route_name = "ai_social_posts.node.{$type->id()}_posts";

      // Check if the route exists before creating a derivative.
      try {
        if ($this->routeProvider->getRouteByName($route_name)) {
          $this->derivatives[$type->id()] = array_merge($base_plugin_definition, [
            'title' => $type->label(),
            'route_name' => $route_name,
            'parent_id' => 'ai_social_posts.node.ai_social_posts',
          ]);
        }
      }
      catch (\Exception $e) {
        // Route doesn't exist, don't create a derivative.
        \Drupal::logger('ai_social_posts')->notice('Route @route does not exist for local task derivative.', ['@route' => $route_name]);
      }
    }

    return $this->derivatives;
  }

}
