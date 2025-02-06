<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * The arrival class for the module.
 */
class ArrivalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Here we set a unique form id
    // Same name with the routing name.
    return 'parking_arrival_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // The car number.
    $form['car_plate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Car Plate'),
      '#required' => TRUE,
    ];

    // Submit button.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $new_node = Node::create(['type' => 'parking']);
    $timestamp = time();
    $new_node->set('title', $timestamp);
    $new_node->set('field_car_plate', $form_state->getValue('car_plate'));
    $new_node->set('field_datetime_in', $timestamp);
    $new_node->set('field_datetime_out', NULL);
    $new_node->enforceIsNew();
    $new_node->save();

  }

}
