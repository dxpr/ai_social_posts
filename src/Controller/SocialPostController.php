<?php

namespace Drupal\socials\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;

/**
 * Controller for social post functionality.
 */
class SocialPostController extends ControllerBase {

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs a SocialPostController object.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder service.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder) {
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder')
    );
  }

  /**
   * Displays social posts for a node and the creation form.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return array
   *   A render array.
   */
  public function nodeSocialPosts(NodeInterface $node) {
    // Create a new social post entity pre-filled with node reference
    $social_post = $this->entityTypeManager()
      ->getStorage('social_post')
      ->create([
        'node_id' => $node->id(),
        // Add any other default values you need
      ]);

    // Get the form
    $form = $this->entityFormBuilder->getForm($social_post, 'default');

    // Get existing social posts for this node
    $posts = $this->entityTypeManager()
      ->getStorage('social_post')
      ->loadByProperties([
        'node_id' => $node->id(),
      ]);

    // Build the list of existing posts
    $existing_posts = [];
    foreach ($posts as $post) {
      $existing_posts[] = $this->entityTypeManager()
        ->getViewBuilder('social_post')
        ->view($post, 'default');
    }

    return [
      '#theme' => 'socials_node_social_posts',
      '#node' => $node,
      '#form' => $form,
      '#posts' => $existing_posts,
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user.permissions'],
      ],
    ];
  }
}