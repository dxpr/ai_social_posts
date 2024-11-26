<?php

namespace Drupal\socials\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the socials entity edit forms.
 *
 * @ingroup socials
 */
class SocialPostForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\socials\Entity\SocialPost $entity */
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
      $this->messenger()->addMessage($this->t('Created the %label Social Post.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Saved the %label Social Post.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirect('entity.social_post.canonical', ['social_post' => $entity->id()]);
  }

}
