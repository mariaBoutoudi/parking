<?php
 
namespace Drupal\parking\Form;
 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManager;

class DepartureForm extends FormBase {


 /**
  * @return [type]
  */
 public function getFormId() {

  // Here we set a unique form id
  //  Same name with the routing name.
   return 'parking_departure_form';
 }
 

 /**
  * @param array $form
  * @param FormStateInterface $form_state
  * @param null $book_id
  * 
  * @return [type]
  */
 public function buildForm(array $form, FormStateInterface $form_state, $book_id = NULL) {

  // Set a value for the hidden form field 'what'.
  $what = 'checkout';

  // Set the checkout time cause is NULL in the arrival form. 
  $currentTime = time();
  

//  If we already have a car id in the url.
  if($book_id) {

  // Load the node by its id from the url.
  $entityManager = \Drupal::service('entity_type.manager');
  $properties = ['title' => $book_id];
  $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

  // Get from the array [nodeid => {nodeObject}] the nodeObject.
  $node = reset($nodeEntity);


  // In case the node exists.
  if($nodeEntity){

    // Have the car id form field as hidden to be used.
    $form['car_id'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

  // Get the plate number from the node to be shown. 
  // the message with the car plate.
  $plateNum = 'Plate number: ' . $node->get('field_car_plate')->value;

  // Get the cost from the node to be shown.
  // Call the function that gives the total cost.
  // With the datetime in and datetime out which is declared at the begining of buildForm.
  $cost = $this->calculateCost($node->get('field_datetime_in')->value, $currentTime);

  // The message with the total cost.
  $costMessage = 'Total payment: ' .  $cost . ' euros'; 
  

  // This field is used for the node elements to be shown.
  $form['info'] = [
    '#type' => 'markup',  
    '#markup' => '<div id="information"> ' . $plateNum . '<br>' . $costMessage . '</div>',

  ];

  // Check the payment.
  $form['payment'] = [
    '#type' => 'checkbox',
    '#title' => $this->t('Payment'),
    // '#default_value' => 1,
  ];

  // Checkout button.
  $form['actions']['checkout'] = [
  '#type' => 'submit',
  '#value' => $this->t('Checkout'),
  '#button_type' => 'primary',
];

  }

  // If there is no node entity.
  else {

    // The unique id of the car. 
    $form['car_id'] = [
    '#type' => 'number',
    '#title' => $this->t('Car ID'),
    '#required' => TRUE,
  ];

  // Submit button.
  $form['actions']['submit'] = [
  '#type' => 'submit',
  '#value' => $this->t('Search'),
  '#button_type' => 'primary',
];

  }
}

// If book id (in url) does not exist.
 else{

  // The unique id of the car. 
  $form['car_id'] = [
    '#type' => 'number',
    '#title' => $this->t('Car ID'),
    '#required' => TRUE,
  ];

  // Submit button.
  $form['actions']['submit'] = [
  '#type' => 'submit',
  '#value' => $this->t('Search'),
  '#button_type' => 'primary',
];
$what = 'search';

 }

// A hidden field to be used.
// $what has two values.
// Search value if the user is in search form (button).
// Checkout value if the user is in checkout form (button).
// We set the value to be used in submitForm function.
 $form['what'] = [
  '#type' => 'hidden',
  '#default_value' => $what,
];

   return $form;
 }


//  public function validateForm(array &$form, FormStateInterface $form_state) {

//  }
 


 /**
  * @param array $form
  * @param FormStateInterface $form_state
  * 
  * @return [type]
  */
 public function submitForm(array &$form, FormStateInterface $form_state) {

  // Get the car id from its form fiels.
  $carId = $form_state->getValue('car_id');

  // Load entity type manager service.
  $entityManager = \Drupal::service('entity_type.manager');

  // Load node by its title.
  $properties = ['title' => $carId];
  $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

  // If the user is in form with checkout button.
  if ($form_state->getValue('what') == 'checkout') {

    $node = $entityManager->getStorage('node')->load($carId);
    $in = $node->get('field_datetime_in')->value;
    $out = time();
    $cost = $this->calculateCost($in, $out);

  // Update the specific noad with the values of the form fields. 
  $update_node = Node::load($carId);
  $timestamp = time();
  $update_node->set('field_datetime_out', $timestamp);
  $update_node->set('field_payment', $form_state->getValue('payment'));
  $update_node->set('field_cost', $cost);
  $update_node->save();

  }

  else{

  // If we already have a node saved. 
  if($nodeEntity) {

  // Redirect to routing 'parking_departure_form'.
  // Path: '/departureform/book_id'
  $form_state->setRedirect('parking_departure_form', ['book_id' => $form_state->getValue('car_id')]);
  }

  // If the Car id does not exist.
  else {

  // Show a message.
  \Drupal::messenger()->addMessage($this->t("This car id does not exist."));

  // Redirect to search form.
  $form_state->setRedirect('parking_departure_form');
  }
}
  return;
 }


 /**
  * Calculate the cost that car must pay.
  *
  * @param int $checkin
  *   The arrival time.
  * @param int $checkout
  *   The departure time.
  * 
  * @return string
  */
public function calculateCost($checkIn, $checkOut) {

  // The cost for the first hour.
  $firstHour = 5; 

  // The cost for each hour
  $pricePerHour = 3;

  // Calculate the time the car was in parking.
  // The values are in timestamp.
  $seconds = $checkOut - $checkIn;

  // Convert the timestamp seconds in hours.
  $hours = $seconds / 3600;

  // Round up the hours.
  // Extract the first hour cause the cost is different.
  // Multiply the left hours with the price per hour.
  // Add to final cost (in euros) the first hour price.
$cost = ((ceil($hours) - 1) * $pricePerHour) + $firstHour;
return $cost;


}



}
