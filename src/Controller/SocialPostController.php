<?php

namespace Drupal\socials\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Returns responses for Social Post routes.
 */
class SocialPostController extends ControllerBase {

  /**
   * Displays social posts overview for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return array
   *   A render array.
   */
  public function nodeSocialPosts(NodeInterface $node) {
    $build = [
      '#theme' => 'social_posts_overview',
      '#node' => $node,
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user.permissions'],
      ],
      '#title' => $this->t('Social Posts for @title', ['@title' => $node->getTitle()]),
    ];

    return $build;
  }

  /**
   * Displays social posts of a specific bundle associated with a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $bundle
   *   The bundle type.
   *
   * @return array
   *   A render array.
   */
  public function nodeBundlePosts(NodeInterface $node, $bundle) {
    $social_post_storage = $this->entityTypeManager()->getStorage('social_post');
    
    // Load existing social posts for this node and bundle
    $query = $social_post_storage->getQuery()
      ->condition('node_id', $node->id())
      ->condition('type', $bundle)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);

    $ids = $query->execute();
    $social_posts = $social_post_storage->loadMultiple($ids);

    $build = [
      '#theme' => 'social_posts_bundle_tab',
      '#node' => $node,
      '#social_posts' => $social_posts,
      '#bundle' => $bundle,
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user.permissions'],
      ],
    ];

    // Add create button
    $build['add_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new @type', [
        '@type' => $this->entityTypeManager()
          ->getStorage('social_post_type')
          ->load($bundle)
          ->label(),
      ]),
      '#url' => Url::fromRoute('socials.social_post_add', [
        'social_post_type' => $bundle,
        'node' => $node->id(),
      ]),
      '#attributes' => [
        'class' => ['button', 'button--action', 'button--primary'],
      ],
    ];

    return $build;
  }

  /**
   * Title callback for the add social post form.
   *
   * @param string $social_post_type
   *   The social post type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle($social_post_type) {
    $type = $this->entityTypeManager()
      ->getStorage('social_post_type')
      ->load($social_post_type);
    return $this->t('Add @type', ['@type' => $type->label()]);
  }

  /**
   * Returns the title for the social posts overview page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return string
   *   The page title.
   */
  public function getSocialPostsTitle(NodeInterface $node) {
    return $this->t('Social Posts for @title', ['@title' => $node->getTitle()]);
  }

  /**
   * Returns the title for the bundle-specific posts page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $bundle
   *   The bundle type.
   *
   * @return string
   *   The page title.
   */
  public function getBundlePostsTitle(NodeInterface $node, $bundle) {
    $type = $this->entityTypeManager()->getStorage('social_post_type')->load($bundle);
    return $this->t('@bundle Posts for @title', [
      '@bundle' => $type->label(),
      '@title' => $node->getTitle(),
    ]);
  }

}