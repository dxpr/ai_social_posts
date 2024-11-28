<?php

namespace Drupal\socials\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for socials entity.
 *
 * @ingroup socials
 */
class SocialPostListBuilder extends EntityListBuilder {

  /**
   * Allowed HTML tags for post content.
   *
   * @var array
   */
  protected const ALLOWED_HTML_TAGS = [
    'p', 'br', 'strong', 'em', 'b', 'i', 'u',
    'ul', 'ol', 'li', 'blockquote', 'a',
    'h2', 'h3', 'h4', 'h5', 'h6',
  ];

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
   * Constructs a new SocialPostListBuilder object.
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
      '#markup' => $this->t('Social Posts allow you to share content across different platforms. Each post can be customized with different fields to match your needs. You can customize these fields in the <a href=":admin_link">Social Posts settings</a>.', [
        ':admin_link' => $this->urlGenerator->generateFromRoute('socials.social_post_settings'),
      ]),
    ];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the social_post list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Post ID');
    $header['type'] = $this->t('Platform');
    $header['post'] = $this->t('Post');
    $header['referenced_node'] = $this->t('Connected Content');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\socials\Entity\SocialPost $entity */
    $row['id'] = $entity->id();
    $row['type'] = $entity->bundle();
    $row['post'] = [
      'data' => [
        '#type' => 'markup',
        '#markup' => strip_tags(
          $entity->get('post')->value,
          '<' . implode('><', static::ALLOWED_HTML_TAGS) . '>'
        ),
        '#allowed_tags' => static::ALLOWED_HTML_TAGS,
      ],
    ];

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

}
