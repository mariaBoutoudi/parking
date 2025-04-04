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

  
    // Set a value for the of car id.
    $id = $form_state->getValue('car_plate');
    
    // Remove - or space from the car id string.
    $str_car = str_replace([" ", "-"], "", $id);

    // The string should have 7 characters.
    // In case there are 7 characters.
    if(strlen($str_car) == "7"){

      // Convert the car id into an array.
      $array_car = str_split($str_car);

    
      // Loop through the car id array of 7 characters.
      // We will use both the key and value of the array.
      foreach($array_car as $key => $item){

        
        // The first 3 must be alphabetic.
        // The last 4 must be numbers.
        // Check the first three items of the array by their key.
        if($key < 3){
      
          // In case the first 3 are not alphabetic.
          if(!ctype_alpha($item)){
          // if(!preg_match('/^[Α-Ωα-ω]+$/u',$item)){

            // Error message.
            $form_state->setErrorByName('car_plate', $this->t('The car plate id is not valid because @val is not a valid character.', ['@val' => $item]));
       }
      }
      // Check the last 4 items of the array by their key.
      else{
        
        // In case the last 4 characters are not numbers.
        if(!is_numeric($item)){

          // Error message.
          $form_state->setErrorByName('car_plate', $this->t('The car plate id is not valid because @val is not a number.',['@val' => $item]));
        }
      }
    }
  }

  // In case there are more or less (than 7) characters in the car id string.
  else{

    // Error message.
    $form_state->setErrorByName('car_plate', $this->t('The car plate id is not valid because @val is more than 7 characters.',['@val' => strlen($str_car)]));
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
    $new_node->set('field_car_plate', $form_state->getValue('car_plate'));
    $new_node->set('field_datetime_in', $timestamp);
    $new_node->set('field_datetime_out', NULL);
    $new_node->enforceIsNew();
    $new_node->save();

  //  A message shown after the submission.
    \Drupal::messenger()->addMessage($this->t("The car number has been saved."));


  }

}
