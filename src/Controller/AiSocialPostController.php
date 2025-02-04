<?php

namespace Drupal\ai_social_posts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for AI Social Post routes.
 */
class AiSocialPostController extends ControllerBase {

  /**
   * The path current service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new AiSocialPostController.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(CurrentPathStack $current_path, RendererInterface $renderer) {
    $this->currentPath = $current_path;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.current'),
      $container->get('renderer')
    );
  }

  /**
   * Displays AI Social Posts overview for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return array
   *   A render array.
   */
  public function nodeAiSocialPosts(NodeInterface $node) {
    // Get all enabled social post types.
    $types = $this->entityTypeManager()
      ->getStorage('ai_social_post_type')
      ->loadMultiple();

    // If no social post types are enabled, show a message.
    if (empty($types)) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No social post types are enabled. <a href=":url">Enable social post types</a>.', [
          ':url' => Url::fromRoute('entity.ai_social_post_type.collection')->toString(),
        ]),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
    }

    // If there's only one type, show its content directly.
    if (count($types) === 1) {
      $type = reset($types);
      return $this->nodeBundlePosts($node, $type->id());
    }

    // If multiple types, show overview with first bundle content.
    $first_type = reset($types);
    return $this->nodeBundlePosts($node, $first_type->id());
  }

  /**
   * Loads AI Social Posts for a given node and bundle.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $bundle
   *   The bundle type.
   *
   * @return \Drupal\ai_social_posts\Entity\AiSocialPost[]
   *   An array of social post entities.
   */
  protected function loadPosts(NodeInterface $node, $bundle) {
    return $this->entityTypeManager()->getStorage('ai_social_post')->loadByProperties([
      'type' => $bundle,
      'node_id' => $node->id(),
    ]);
  }

  /**
   * Displays AI Social Posts of a specific bundle for a node.
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
    $ai_social_post = $this->entityTypeManager()->getStorage('ai_social_post')->create([
      'type' => $bundle,
      'node_id' => $node->id(),
    ]);

    // Get bundle info.
    $bundle_type = $this->entityTypeManager()
      ->getStorage('ai_social_post_type')
      ->load($bundle);
    $bundle_label = $bundle_type ? $bundle_type->label() : ucfirst($bundle);

    // Get the form.
    $form = $this->entityFormBuilder()->getForm($ai_social_post);

    // Set the destination to the current page's path.
    $current_path = $this->currentPath->getPath();
    $form['#action'] = $form['#action'] . '?destination=' . urlencode($current_path);

    // Keep existing button text.
    $form['actions']['submit']['#value'] = $this->t('Save @type', [
      '@type' => $bundle_label,
    ]);

    return [
      'form_wrapper' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['ai-social-post-form-wrapper'],
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
          'class' => ['ai-social-posts-list'],
        ],
        'heading' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Created Posts'),
        ],
        'content' => [
          '#theme' => 'ai_social_posts_bundle_tab',
          '#node' => $node,
          '#ai_social_posts' => array_map(function ($post) {
            /** @var \Drupal\ai_social_posts\Entity\AiSocialPost $post */
            // Get the processed text with format.
            $text = [
              '#type' => 'processed_text',
              '#text' => $post->get('post')->value,
              '#format' => $post->get('post')->format ?: filter_default_format(),
            ];

            // Render the text.
            $rendered_text = $this->renderer->renderPlain($text);

            return [
              'id' => [
                '#type' => 'link',
                '#title' => $post->id(),
                '#url' => $post->toUrl(),
              ],
              'post' => [
                'value' => $rendered_text,
              ],
              'created' => [
                'value' => $post->get('created')->value,
              ],
              'operations' => [
                '#type' => 'operations',
                '#links' => [
                  'view' => [
                    'title' => $this->t('View'),
                    'url' => $post->toUrl(),
                  ],
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
        'library' => ['ai_social_posts/ai-social-posts'],
      ],
    ];
  }

  /**
   * Title callback for the add social post form.
   *
   * @param string $ai_social_post_type
   *   The social post type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle($ai_social_post_type) {
    $type = $this->entityTypeManager()
      ->getStorage('ai_social_post_type')
      ->load($ai_social_post_type);
    return $this->t('Add @type', ['@type' => $type->label()]);
  }

  /**
   * Returns the title for the AI Social Posts overview page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return string
   *   The page title.
   */
  public function getAiSocialPostsTitle(NodeInterface $node) {
    return $this->t('AI Social Posts for @title', ['@title' => $node->getTitle()]);
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
    $type = $this->entityTypeManager()->getStorage('ai_social_post_type')->load($bundle);
    return $this->t('@bundle Posts for @title', [
      '@bundle' => $type->label(),
      '@title' => $node->getTitle(),
    ]);
  }

}
