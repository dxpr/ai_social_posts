<?php

namespace Drupal\socials\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\socials\SocialPostInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Social Post entity.
 *
 * @ContentEntityType(
 *   id = "social_post",
 *   label = @Translation("Social Post"),
 *   bundle_label = @Translation("Social Post Type"),
 *   base_table = "social_post",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\socials\Entity\Controller\SocialPostListBuilder",
 *     "form" = {
 *       "default" = "Drupal\socials\Form\SocialPostForm",
 *       "add" = "Drupal\socials\Form\SocialPostForm",
 *       "edit" = "Drupal\socials\Form\SocialPostForm",
 *       "delete" = "Drupal\socials\Form\SocialPostDeleteForm",
 *     },
 *     "access" = "Drupal\socials\SocialPostAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer social post entity",
 *   bundle_entity_type = "social_post_type",
 *   field_ui_base_route = "entity.social_post_type.edit_form",
 *   links = {
 *     "canonical" = "/social_post/{social_post}",
 *     "add-page" = "/social_post/add",
 *     "add-form" = "/social_post/add/{social_post_type}",
 *     "edit-form" = "/social_post/{social_post}/edit",
 *     "delete-form" = "/social_post/{social_post}/delete",
 *     "collection" = "/admin/content/social-posts"
 *   }
 * )
 */
class SocialPost extends ContentEntityBase implements SocialPostInterface {

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
      \Drupal::logger('socials')->error(
        'Failed to generate URL for node @nid: @message', [
          '@nid' => $node->id(),
          '@message' => $e->getMessage(),
        ]
      );
      return;
    }

    // Get bundle-specific field definitions.
    $fields = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions('social_post', $values['type']);

    // Iterate over configurable fields.
    foreach ($fields as $field_name => $field) {
      if (!$field instanceof FieldConfig) {
        continue;
      }

      $default = $field->getDefaultValueLiteral();
      if (empty($default[0]['value'])) {
        continue;
      }

      // Preserve original field settings.
      $values[$field_name] = $default[0];

      // Single translatable string with URL and prompt placeholders.
      $values[$field_name]['value'] = sprintf(
        '/%s',
        t('For :url @prompt. Include the link.', [
          ':url' => $url,
          '@prompt' => ltrim($default[0]['value'], '/ '),
        ], ['context' => 'Social post with URL'])
      );
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
