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

    // The vehicle number.
    $form['vehicle_plate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vehicle Plate'),
      '#required' => TRUE,

    ];


    // The time type for the vehicle.
    $form['field_time_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select type of time'),
      '#default_value' => 'per_hour',
      '#options' => [
          'per_hour' => $this->t('Per hour'),
          'per_day' => $this->t('Per day'),
      ],

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

    // Set a value for the of car id.
    $id = $form_state->getValue('vehicle_plate');
    
    // Remove "-" or space from the car id string.
    $str_vehicle = str_replace([" ", "-"], "", $id);

    // The string should have 7 characters.

    // In case there are 7 characters.
    if(strlen($str_vehicle) == "7"){

    // Convert the car id into an array.
    $array_vehicle = str_split($str_vehicle);

    // Loop through the car id array of 7 characters.
    // We will use both the key and value of the array.
    foreach($array_vehicle as $key => $item){

    // The first 3 must be alphabetic.
    // The last 4 must be numbers.
    // Check the first three items of the array by their key.
    if($key < 3){
  
    // In case the first 3 are not latin characters.
    $pattern = '/^[a-zA-Z]+$/';
    if(!preg_match($pattern, $item)){
   
    // Error message.
    $form_state->setErrorByName('vehicle_plate', 
    $this->t('The vehicle plate id is not valid because @val is not a valid character.', ['@val' => $item]));
    }
  }
    // Check the last 4 items of the array by their key.
    else{
    
    // In case the last 4 characters are not numbers.
    if(!is_numeric($item)){

    // Error message.
    $form_state->setErrorByName('vehicle_plate',
    $this->t('The vehicle plate id is not valid because @val is not a number.',['@val' => $item]));
    }
  }
    }
  }

  // In case there are more or less (than 7) characters in the car id string.
  else{

  // Error message.
  $form_state->setErrorByName('vehicle_plate', 
  $this->t('The vehicle plate must have 7 characters.'));
  }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Create a node after submission.
    // Node fields: car plate, date/time of car arrival.
    // Set as null the date/time of car departure.
    $new_node = Node::create(['type' => 'parking']);
    $timestamp = time();
    $new_node->set('title', $timestamp);
    $new_node->set('field_car_plate', $form_state->getValue('vehicle_plate'));
    $new_node->set('field_datetime_in', $timestamp);
    $new_node->set('field_datetime_out', NULL);
    $new_node->set('field_time_type', $form_state->getValue('field_time_type'));
    $new_node->enforceIsNew();
    $new_node->save();

  //  A message shown after the submission.
    \Drupal::messenger()->addMessage($this->t('The vehicle plate has been saved with the id ' .  $timestamp));

    // Redirect to dashboard page.  
    $form_state->setRedirect('parking_dashboard');

  }

}
