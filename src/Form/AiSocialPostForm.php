<?php

namespace Drupal\ai_social_posts\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the ai_social_posts entity edit forms.
 *
 * @ingroup ai_social_posts
 */
class AiSocialPostForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\ai_social_posts\Entity\AiSocialPost $entity */
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label AI Social Post.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Saved the %label AI Social Post.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirect('entity.ai_social_post.canonical', ['ai_social_post' => $entity->id()]);
  }

}
