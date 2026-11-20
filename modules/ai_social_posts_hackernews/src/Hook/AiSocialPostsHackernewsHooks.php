<?php

namespace Drupal\ai_social_posts_hackernews\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for ai_social_posts_hackernews.
 */
class AiSocialPostsHackernewsHooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form_object = $form_state->getFormObject();
    // Check if this is an entity form for a Hacker News post.
    if ($form_object instanceof EntityFormInterface) {
      $entity = $form_object->getEntity();
      if ($entity->getEntityTypeId() === 'ai_social_post' && $entity->bundle() === 'hackernews_post') {
        // Add our custom library to the form.
        $form['#attached']['library'][] = 'ai_social_posts_hackernews/hackernews-form';
        // Add a class to the form for easier targeting in JavaScript.
        $form['#attributes']['class'][] = 'ai-social-post-hackernews-post-form';
      }
    }
  }

}
