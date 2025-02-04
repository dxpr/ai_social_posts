<?php

namespace Drupal\ai_social_posts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the AI Social Post Type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "ai_social_post_type",
 *   label = @Translation("AI Social Post Type"),
 *   bundle_of = "ai_social_post",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_prefix = "ai_social_post_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid"
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\ai_social_posts\Entity\Controller\AiSocialPostTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\ai_social_posts\Form\AiSocialPostTypeForm",
 *       "add" = "Drupal\ai_social_posts\Form\AiSocialPostTypeForm",
 *       "edit" = "Drupal\ai_social_posts\Form\AiSocialPostTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   admin_permission = "administer social post entity",
 *   links = {
 *     "canonical" = "/admin/structure/ai_social_post_types/{ai_social_post_type}",
 *     "add-form" = "/admin/structure/ai_social_post_types/add",
 *     "edit-form" = "/admin/structure/ai_social_post_types/{ai_social_post_type}/edit",
 *     "delete-form" = "/admin/structure/ai_social_post_types/{ai_social_post_type}/delete",
 *     "collection" = "/admin/structure/ai_social_post_types"
 *   }
 * )
 */
class AiSocialPostType extends ConfigEntityBundleBase {
}
