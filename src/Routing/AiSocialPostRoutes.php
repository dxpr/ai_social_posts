<?php

namespace Drupal\ai_social_posts\Routing;

use Drupal\ai_social_posts\AiSocialPostTypeManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines dynamic routes for AI Social Posts.
 */
class AiSocialPostRoutes implements ContainerInjectionInterface {

  /**
   * The social post type manager.
   *
   * @var \Drupal\ai_social_posts\AiSocialPostTypeManager
   */
  protected $postTypeManager;

  /**
   * Constructs a new AiSocialPostRoutes object.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ai_social_posts.post_type_manager')
    );
  }

  /**
   * Returns a collection of routes.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   A route collection.
   */
  public function routes() {
    $collection = new RouteCollection();

    // Add the overview route.
    $collection->add('ai_social_posts.node.ai_social_posts', new Route(
      '/admin/content/node/{node}/ai-social-posts',
      [
        '_controller' => '\Drupal\ai_social_posts\Controller\AiSocialPostController::nodeAiSocialPosts',
        '_title_callback' => '\Drupal\ai_social_posts\Controller\AiSocialPostController::getAiSocialPostsTitle',
      ],
      [
        '_permission' => 'view ai_social_post entity',
      ],
      [
        'parameters' => [
          'node' => ['type' => 'entity:node'],
        ],
        '_admin_route' => TRUE,
      ]
    ));

    // Add routes for each post type.
    foreach ($this->postTypeManager->getTypes() as $type) {
      $collection->add("ai_social_posts.node.{$type->id()}_posts", new Route(
        "/admin/content/node/{node}/ai-social-posts/{$type->id()}",
        [
          '_controller' => '\Drupal\ai_social_posts\Controller\AiSocialPostController::nodeBundlePosts',
          '_title_callback' => '\Drupal\ai_social_posts\Controller\AiSocialPostController::getBundlePostsTitle',
          'bundle' => $type->id(),
        ],
        [
          '_permission' => 'view ai_social_post entity',
        ],
        [
          'parameters' => [
            'node' => ['type' => 'entity:node'],
          ],
          '_admin_route' => TRUE,
        ]
      ));
    }

    return $collection;
  }

}
