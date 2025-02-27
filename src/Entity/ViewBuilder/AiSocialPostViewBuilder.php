<?php

namespace Drupal\ai_social_posts\Entity\ViewBuilder;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * View builder for AI Social Post entities.
 */
class AiSocialPostViewBuilder extends EntityViewBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new AiSocialPostViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Theme\Registry $theme_registry
   *   The theme registry.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    Registry $theme_registry = NULL,
    EntityDisplayRepositoryInterface $entity_display_repository = NULL,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($entity_type, $entity_repository, $language_manager, $theme_registry, $entity_display_repository);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_display.repository'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    // Add author information if available.
    if ($entity->hasField('user_id') && !$entity->get('user_id')->isEmpty()) {
      $author = $entity->getOwner();
      if ($author) {
        $build['author'] = [
          '#type' => 'item',
          '#title' => t('Author'),
          '#markup' => $author->getDisplayName(),
          '#weight' => -10,
        ];
      }
    }

    // Add created date information.
    if ($entity->hasField('created') && !$entity->get('created')->isEmpty()) {
      $created_timestamp = $entity->get('created')->value;
      $build['created'] = [
        '#type' => 'item',
        '#title' => t('Created'),
        '#markup' => $this->dateFormatter->format($created_timestamp, 'medium'),
        '#weight' => -9,
      ];
    }

    return $build;
  }

} 