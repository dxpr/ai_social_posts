<?php

namespace Drupal\socials\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Social Post Type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "social_post_type",
 *   label = @Translation("Social Post Type"),
 *   bundle_of = "social_post",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_prefix = "social_post_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid"
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\socials\Entity\Controller\SocialPostTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\socials\Form\SocialPostTypeForm",
 *       "add" = "Drupal\socials\Form\SocialPostTypeForm",
 *       "edit" = "Drupal\socials\Form\SocialPostTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   admin_permission = "administer social post entity",
 *   links = {
 *     "canonical" = "/admin/structure/social_post_types/{social_post_type}",
 *     "add-form" = "/admin/structure/social_post_types/add",
 *     "edit-form" = "/admin/structure/social_post_types/{social_post_type}/edit",
 *     "delete-form" = "/admin/structure/social_post_types/{social_post_type}/delete",
 *     "collection" = "/admin/structure/social_post_types"
 *   }
 * )
 */
class SocialPostType extends ConfigEntityBundleBase {
}
