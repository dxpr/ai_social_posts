<?php

namespace Drupal\socials\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Drupal\socials\SocialPostTypeManager;

/**
 * Defines dynamic routes for social posts.
 */
class SocialPostRoutes implements ContainerInjectionInterface {

  /**
   * The social post type manager.
   *
   * @var \Drupal\socials\SocialPostTypeManager
   */
  protected $postTypeManager;

  /**
   * Constructs a new SocialPostRoutes object.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('socials.post_type_manager')
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

    // Add the overview route
    $collection->add('socials.node.social_posts', new Route(
      '/node/{node}/social-posts',
      [
        '_controller' => '\Drupal\socials\Controller\SocialPostController::nodeSocialPosts',
        '_title_callback' => '\Drupal\socials\Controller\SocialPostController::getSocialPostsTitle',
      ],
      [
        '_permission' => 'view social_post entity',
      ],
      [
        'parameters' => [
          'node' => ['type' => 'entity:node'],
        ],
      ]
    ));

    // Add routes for each post type
    foreach ($this->postTypeManager->getTypes() as $type) {
      $collection->add("socials.node.{$type->id()}_posts", new Route(
        "/node/{node}/social-posts/{$type->id()}",
        [
          '_controller' => '\Drupal\socials\Controller\SocialPostController::nodeBundlePosts',
          '_title_callback' => '\Drupal\socials\Controller\SocialPostController::getBundlePostsTitle',
          'bundle' => $type->id(),
        ],
        [
          '_permission' => 'view social_post entity',
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