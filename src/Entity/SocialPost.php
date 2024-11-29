<?php

namespace Drupal\socials\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
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
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    $route_match = \Drupal::routeMatch();

    if ($node = $route_match->getParameter('node')) {
      $url = $node->toUrl()->setAbsolute()->toString();
      $type = str_replace('_post', '', $values['type']);

      // Load module-specific prompts.
      $config = \Drupal::config("socials_{$type}.prompts");
      $prompts = $config->get('prompts') ?? [];

      // Set default values for title field.
      $values['title'] = [
        'value' => '/' . sprintf(
          t('Write a @type title for @url@suffix', [
            '@type' => $type,
            '@url' => $url,
            '@suffix' => $prompts['title'] ?? '',
          ])
        ),
        'format' => 'basic_html',
      ];

      // Set default values for subtitle field (Substack only)
      if ($type === 'substack') {
        $values['subtitle'] = [
          'value' => '/' . sprintf(
            t('Write a subtitle for @url@suffix', [
              '@url' => $url,
              '@suffix' => $prompts['subtitle'] ?? '',
            ])
          ),
          'format' => 'basic_html',
        ];
      }

      // Set default values for post field.
      $values['post'] = [
        'value' => '/' . sprintf(
          t('Create a @type post for @url@suffix', [
            '@type' => $type,
            '@url' => $url,
            '@suffix' => $prompts['post'] ?? '',
          ])
        ),
        'format' => 'basic_html',
      ];
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
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behavior of the used widgets can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['node_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node'))
      ->setDescription(t('The node this social post belongs to.'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the social post was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the social post was last edited.'));

    return $fields;
  }

}
