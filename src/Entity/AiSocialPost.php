<?php

namespace Drupal\ai_social_posts\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\ai_social_posts\AiSocialPostInterface;
use Drupal\user\UserInterface;

/**
 * Defines the AI Social Post entity.
 *
 * @ContentEntityType(
 *   id = "ai_social_post",
 *   label = @Translation("AI Social Post"),
 *   bundle_label = @Translation("AI Social Post Type"),
 *   base_table = "ai_social_post",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ai_social_posts\Entity\Controller\AiSocialPostListBuilder",
 *     "form" = {
 *       "default" = "Drupal\ai_social_posts\Form\AiSocialPostForm",
 *       "add" = "Drupal\ai_social_posts\Form\AiSocialPostForm",
 *       "edit" = "Drupal\ai_social_posts\Form\AiSocialPostForm",
 *       "delete" = "Drupal\ai_social_posts\Form\AiSocialPostDeleteForm",
 *     },
 *     "access" = "Drupal\ai_social_posts\AiSocialPostAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer ai_social_post entity",
 *   bundle_entity_type = "ai_social_post_type",
 *   field_ui_base_route = "entity.ai_social_post_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/ai-social-posts/{ai_social_post}",
 *     "add-page" = "/admin/content/ai-social-posts/add",
 *     "add-form" = "/admin/content/ai-social-posts/add/{ai_social_post_type}",
 *     "edit-form" = "/admin/content/ai-social-posts/{ai_social_post}/edit",
 *     "delete-form" = "/admin/content/ai-social-posts/{ai_social_post}/delete",
 *     "collection" = "/admin/content/ai-social-posts"
 *   }
 * )
 */
class AiSocialPost extends ContentEntityBase implements AiSocialPostInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new social post is created from a node, prefixes the default field
   * values with the node's URL.
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    // Only process if we have a node context.
    if (empty($values['type'])) {
      return;
    }

    $route_match = \Drupal::routeMatch();
    $node = $route_match->getParameter('node');
    if (!$node) {
      return;
    }

    try {
      $url = $node->toUrl()->setAbsolute()->toString();
    }
    catch (\Exception $e) {
      \Drupal::logger('ai_social_posts')->error(
        'Failed to generate URL for node @nid: @message', [
          '@nid' => $node->id(),
          '@message' => $e->getMessage(),
        ]
      );
      return;
    }

    // Get the field definition for the 'post' field.
    $field_manager = \Drupal::service('entity_field.manager');
    $fields = $field_manager->getFieldDefinitions('ai_social_post', $values['type']);
    
    // Only process the 'post' field if it exists and has a default value.
    if (isset($fields['post']) && $fields['post'] instanceof FieldConfig) {
      $default = $fields['post']->getDefaultValueLiteral();
      if (!empty($default[0]['value'])) {
        // Preserve original field settings.
        $values['post'] = $default[0];
        
        // Add URL and prompt to the 'post' field.
        $values['post']['value'] = sprintf(
          '/%s',
          t('For :url @prompt. Include the link.', [
            ':url' => $url,
            '@prompt' => ltrim($default[0]['value'], '/ '),
          ], ['context' => 'Social post with URL'])
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['node_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Node'))
      ->setDescription(new TranslatableMarkup('The node this social post belongs to.'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the social post was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the social post was last edited.'));

    return $fields;
  }

}
