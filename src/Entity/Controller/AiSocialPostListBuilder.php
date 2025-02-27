<?php

namespace Drupal\ai_social_posts\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for ai_social_posts entity.
 *
 * @ingroup ai_social_posts
 */
class AiSocialPostListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new AiSocialPostListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('AI Social Posts allow you to share content across different platforms. Each post can be customized with different fields to match your needs. You can customize these fields in the <a href=":admin_link">AI Social Posts settings</a>.', [
        ':admin_link' => $this->urlGenerator->generateFromRoute('ai_social_posts.ai_social_post_settings'),
      ]),
    ];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the ai_social_post list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = [
      'data' => $this->t('Post ID'),
      'field' => 'id',
      'specifier' => 'id',
      'sort' => 'asc',
    ];
    $header['type'] = [
      'data' => $this->t('Platform'),
      'field' => 'type',
      'specifier' => 'type',
    ];
    $header['post'] = [
      'data' => $this->t('Post'),
      'field' => 'post',
      'specifier' => 'post__value',
    ];
    $header['author'] = [
      'data' => $this->t('Author'),
      'field' => 'user_id',
      'specifier' => 'user_id__target_id',
    ];
    $header['created'] = [
      'data' => $this->t('Created'),
      'field' => 'created',
      'specifier' => 'created',
      'sort' => 'desc',
    ];
    $header['referenced_node'] = [
      'data' => $this->t('Connected Content'),
      'field' => 'node_id',
      'specifier' => 'node_id__target_id',
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\ai_social_posts\Entity\AiSocialPost $entity */
    $row['id'] = [
      'data' => [
        '#type' => 'link',
        '#title' => $entity->id(),
        '#url' => $entity->toUrl(),
      ],
    ];
    $row['type'] = $entity->bundle();

    // Get the processed text with format.
    $text = [
      '#type' => 'processed_text',
      '#text' => $entity->get('post')->value,
      '#format' => $entity->get('post')->format ?: filter_default_format(),
    ];

    // Render and trim the text.
    $rendered_text = trim(\Drupal::service('renderer')->renderPlain($text));
    $trimmed_text = strlen($rendered_text) > 350
      ? substr($rendered_text, 0, 347) . '...'
      : $rendered_text;

    $row['post'] = [
      'data' => [
        '#markup' => $trimmed_text,
      ],
    ];

    // Add author information.
    if ($entity->hasField('user_id') && !$entity->get('user_id')->isEmpty()) {
      $author = $entity->getOwner();
      $row['author'] = $author ? $author->getDisplayName() : '';
    }
    else {
      $row['author'] = '';
    }

    // Add created date.
    if ($entity->hasField('created') && !$entity->get('created')->isEmpty()) {
      $created_timestamp = $entity->get('created')->value;
      $row['created'] = \Drupal::service('date.formatter')->format($created_timestamp, 'short');
    }
    else {
      $row['created'] = '';
    }

    // Safely get the referenced node.
    if ($entity->hasField('node_id') && !$entity->get('node_id')->isEmpty()) {
      $node = $entity->get('node_id')->entity;
      $row['referenced_node'] = $node ? $node->toLink() : '';
    }
    else {
      $row['referenced_node'] = '';
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE);

    // Add the table sort.
    $headers = $this->buildHeader();
    $query->tableSort($headers);

    // Add the pager.
    $query->pager($this->getLimit());

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  protected function getLimit() {
    return 50;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->access('view') && $entity->hasLinkTemplate('canonical')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'weight' => -100,
        'url' => $entity->toUrl(),
      ];
    }

    return $operations;
  }

}
