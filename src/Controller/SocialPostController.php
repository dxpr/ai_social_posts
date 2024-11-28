<?php

namespace Drupal\socials\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Social Post routes.
 */
class SocialPostController extends ControllerBase {

  /**
   * The path current service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a new SocialPostController.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   */
  public function __construct(CurrentPathStack $current_path) {
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.current')
    );
  }

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
   * Loads social posts for a given node and bundle.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $bundle
   *   The bundle type.
   *
   * @return \Drupal\socials\Entity\SocialPost[]
   *   An array of social post entities.
   */
  protected function loadPosts(NodeInterface $node, $bundle) {
    return $this->entityTypeManager()->getStorage('social_post')->loadByProperties([
      'type' => $bundle,
      'node_id' => $node->id(),
    ]);
  }

  /**
   * Displays social posts of a specific bundle for a node.
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
    // Create a new social post entity.
    $social_post = $this->entityTypeManager()->getStorage('social_post')->create([
      'type' => $bundle,
      'node_id' => $node->id(),
    ]);

    // Get bundle info.
    $bundle_type = $this->entityTypeManager()
      ->getStorage('social_post_type')
      ->load($bundle);
    $bundle_label = $bundle_type ? $bundle_type->label() : ucfirst($bundle);

    // Get the form.
    $form = $this->entityFormBuilder()->getForm($social_post);

    // Set the destination to the current page's path.
    $current_path = $this->currentPath->getPath();
    $form['#action'] = $form['#action'] . '?destination=' . urlencode($current_path);

    // Keep existing button text.
    $form['actions']['submit']['#value'] = $this->t('Create @type', [
      '@type' => $bundle_label,
    ]);

    return [
      'form_wrapper' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['social-post-form-wrapper'],
        ],
        'heading' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Create @type', [
            '@type' => $bundle_label,
          ]),
        ],
        'description' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('Create a new post for @title', [
            '@title' => $node->getTitle(),
          ]),
          '#attributes' => [
            'class' => ['form-description'],
          ],
        ],
        'form' => $form,
      ],
      'existing_posts' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['social-posts-list'],
        ],
        'heading' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Created Posts'),
        ],
        'content' => [
          '#theme' => 'social_posts_bundle_tab',
          '#node' => $node,
          '#social_posts' => array_map(function ($post) {
            /** @var \Drupal\socials\Entity\SocialPost $post */
            // Get the processed text with format.
            $text = [
              '#type' => 'processed_text',
              '#text' => $post->get('post')->value,
              '#format' => $post->get('post')->format ?: filter_default_format(),
            ];

            // Render the text.
            $rendered_text = $this->renderer()->renderPlain($text);

            return [
              'post' => [
                'value' => $rendered_text,
              ],
              'created' => [
                'value' => $post->get('created')->value,
              ],
              'operations' => [
                '#type' => 'operations',
                '#links' => [
                  'edit' => [
                    'title' => $this->t('Edit'),
                    'url' => $post->toUrl('edit-form'),
                  ],
                  'delete' => [
                    'title' => $this->t('Delete'),
                    'url' => $post->toUrl('delete-form'),
                  ],
                ],
              ],
            ];
          }, $this->loadPosts($node, $bundle)),
          '#bundle' => $bundle,
        ],
      ],
      '#attached' => [
        'library' => ['socials/social-posts'],
      ],
    ];
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
