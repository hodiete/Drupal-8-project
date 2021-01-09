<?php

namespace Drupal\customers\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CustomerEntityTypeForm.
 */
class CustomerEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $customer_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $customer_entity_type->label(),
      '#description' => $this->t("Label for the Customers type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $customer_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\customers\Entity\CustomerEntityType::load',
      ],
      '#disabled' => !$customer_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $customer_entity_type = $this->entity;
    $status = $customer_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Customers type.', [
          '%label' => $customer_entity_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Customers type.', [
          '%label' => $customer_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($customer_entity_type->toUrl('collection'));
  }

}
