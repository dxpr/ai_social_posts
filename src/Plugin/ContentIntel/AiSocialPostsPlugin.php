<?php

declare(strict_types=1);

namespace Drupal\ai_social_posts\Plugin\ContentIntel;

use Drupal\content_intel\Attribute\ContentIntel;
use Drupal\content_intel\ContentIntelPluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides AI social posts data for nodes.
 */
#[ContentIntel(
  id: 'ai_social_posts',
  label: new TranslatableMarkup('AI Social Posts'),
  description: new TranslatableMarkup('Generated social media posts linked to content.'),
  entity_types: ['node'],
  weight: 40,
)]
class AiSocialPostsPlugin extends ContentIntelPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected ?EntityTypeManagerInterface $entityTypeManager = NULL;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable(): bool {
    // Check if the ai_social_post entity type exists.
    try {
      $this->entityTypeManager?->getDefinition('ai_social_post');
      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(ContentEntityInterface $entity): bool {
    return $entity instanceof NodeInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(ContentEntityInterface $entity): array {
    if (!$this->entityTypeManager || !$entity instanceof NodeInterface) {
      return [];
    }

    try {
      $storage = $this->entityTypeManager->getStorage('ai_social_post');
    }
    catch (\Exception $e) {
      return [];
    }

    // Load all social posts linked to this node.
    $posts = $storage->loadByProperties(['node_id' => $entity->id()]);

    if (empty($posts)) {
      return [
        'total_posts' => 0,
        'posts' => [],
      ];
    }

    $post_data = [];
    $types_count = [];

    foreach ($posts as $post) {
      $type = $post->bundle();
      $types_count[$type] = ($types_count[$type] ?? 0) + 1;

      // Get timestamps from fields.
      $created = $post->hasField('created') && !$post->get('created')->isEmpty()
        ? (int) $post->get('created')->value
        : 0;
      $changed = $post->getChangedTime();

      $post_info = [
        'id' => $post->id(),
        'type' => $type,
        'created' => $created ? [
          'timestamp' => $created,
          'iso8601' => date('c', $created),
        ] : NULL,
        'changed' => $changed ? [
          'timestamp' => $changed,
          'iso8601' => date('c', $changed),
        ] : NULL,
      ];

      // Get the author.
      if ($post->getOwner()) {
        $post_info['author'] = [
          'uid' => $post->getOwnerId(),
          'name' => $post->getOwner()->getDisplayName(),
        ];
      }

      // Get configurable fields.
      $fields_to_extract = ['post', 'title', 'subtitle'];
      foreach ($fields_to_extract as $field_name) {
        if ($post->hasField($field_name) && !$post->get($field_name)->isEmpty()) {
          $field = $post->get($field_name);
          $post_info[$field_name] = $field->value;
        }
      }

      $post_data[] = $post_info;
    }

    // Sort by created date descending (newest first).
    usort($post_data, fn($a, $b) => $b['created']['timestamp'] <=> $a['created']['timestamp']);

    return [
      'total_posts' => count($posts),
      'by_type' => $types_count,
      'posts' => $post_data,
    ];
  }

}
