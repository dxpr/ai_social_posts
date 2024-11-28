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
