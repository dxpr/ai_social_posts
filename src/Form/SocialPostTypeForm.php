<?php

namespace Drupal\socials\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for social post type forms.
 */
class SocialPostTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity_type->label(),
      '#description' => $this->t("Label for the social post type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\socials\Entity\SocialPostType::load',
      ],
      '#disabled' => !$entity_type->isNew(),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_type = $this->entity;

    $status = $entity_type->save();

    $message_params = [
      '%label' => $entity_type->label(),
    ];

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label social post type.', $message_params));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label social post type.', $message_params));
    }

    $form_state->setRedirectUrl($entity_type->toUrl('collection'));
  }

}
