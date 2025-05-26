<?php

namespace Drupal\generate_vehicles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\parking\Services\ParkingService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The generate class for the module.
 */
class GenerateVehiclesForm extends FormBase {

    /**
   * The calculator.
   *
   * @var \Drupal\parking\Services\ParkingService
   */
  protected $calculator;

  /**
   * The constructor.
   *
   * @param \Drupal\parking\Services\ParkingService $calculator
   *   The calculator.
   */
  public function __construct(ParkingService $calculator) {
    $this->calculator = $calculator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('parking.calculation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Here we set a unique form id
    // Same name with the routing name.
    return 'generate_vehicles_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // The number of vehicles.
    $form['vehicles_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Vehicles (1-20)'),
      '#description' => $this->t('Please fill in the number of vehicles to generate.'),
      '#required' => TRUE,

    ];

    // Submit button.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  // Get the number of vehicles from the form field.  
  $numOfVehicles = $form_state->getValue('vehicles_number');

  
  // The number of vehicles to generate are 1-20.
  // In case the number of vehicles are more than 20.
  // Or equal to zero.
  if($numOfVehicles > 20 || $numOfVehicles == 0){
              // Error message.
            $form_state->setErrorByName('vehicles_number',
            $this->t('The number of vehicles must be from 1 to 20'));
    }

    }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the number of vehicles from the form field.  
    $numOfVehicles = $form_state->getValue('vehicles_number');

    // Split total nodes in two parts.
    // Some nodes for current day (2/3).
    // Some for previous days (1/3).

    // Nodes for previous day.
    $previousDay = $numOfVehicles / 3;

    // Nodes for current day.
    $currentDay = $numOfVehicles -$previousDay;

    // Get random vehicle plates.
    $randomVehiclePlate = $this->generateRandomVehiclePlates();

    // Get random node title and 
    // date/time the vehicle arrives in parking.
    $randomDateIn = $this->generateRandomDate();

    // Get random value for time type.
    $typeValues = ['per_hour', 'per_day'];
    $randomKey = random_int(0, 1);
    $randomTimeType = $typeValues[$randomKey];

    // The time vehicle departures.
    $dateTimeOut = $randomDateIn + (3600*2);

    // Get random value for departure time.
    $departureValues = [NULL, $dateTimeOut];
    $randTimeKey = random_int(0, 1);
    $randomDateOut = $departureValues[$randTimeKey];

    // Set a variable for random payment. 
    $randomPayment= '';

    // In case the vehicle leaves the parking.
    if($randomDateOut != NULL){

    // Get random payment value.
    $randomPayment = random_int(0, 1);
    }

      // In case the vehicle is still in parking. 
      else {

        // The payment is 'No'.
        $randomPayment == 0;
      }
    
      // Set a variable for the total cost.
      $cost = "";

      // If the vehicle is checking out.
      if($randomDateOut != NULL){
      
        // The time type is per hour
        if($randomTimeType == 'per_hour'){

          // Service for calculating the cost per hour.
          $cost = $this->calculator->calculateCostPerHour($randomDateIn, $randomDateOut);
        }

        // The time type per day.
        else{

          // Service for calculating the cost per day..
          $cost = $this->calculator->calculateCostPerDay($randomDateIn, $randomDateOut);
      }
    }


    // Generate the nodes after submission.
    $new_node = Node::create(['type' => 'parking']);
    $new_node->set('title', $randomDateIn);
    $new_node->set('field_car_plate', $randomVehiclePlate);
    $new_node->set('field_datetime_in', $randomDateIn);
    $new_node->set('field_datetime_out', $randomDateOut);
    $new_node->set('field_time_type', $randomTimeType);
    $new_node->set('field_payment', $randomPayment);
    $new_node->set('field_cost', $cost);
    $new_node->enforceIsNew();
    $new_node->save();

    // A message shown after the submission.
    \Drupal::messenger()->addMessage($this->t('The number of vehicles has been created.'));

    // Redirect to dashboard page.
    $form_state->setRedirect('parking_dashboard');

  }

/**
 * Generate random vehicle plates.
 * 
 * @return string
 */
protected function generateRandomVehiclePlates(){

  // The letters for the vehicle plate.
  $letters = 'ABEHIKMNOPTXYZ';

  // The length of $letters string.
  $lettersLength = strlen($letters);

  // Set a value for the random letters.
  $randomLetters = '';

    // Loop through the letters.
    // Choose random.
    for ($i = 0; $i < 3; $i++) {
        $randomLetters .= $letters[random_int(0, $lettersLength - 1)];
    }

  // The numbers for the vehicle plate.
  $numbers = '0123456789';

  // The length of $numbers string.
  $numbersLength = strlen($numbers);

  // Set a value for the random numbers.
  $randomNumbers = '';

    // Loop through the numbers.
    // Choose random.
    for ($j = 0; $j < 4; $j++) {
        $randomNumbers .= $numbers[random_int(0, $numbersLength - 1)];
    }

    // The final random vehicle plate.
    $vehiclePlate = $randomLetters . $randomNumbers;

    // Return the vehicle plate.
    return $vehiclePlate;

}

/**
 * Generate random date.
 * 
 * @return string
 */
protected function generateRandomDate(){

  // Current time.
  $timestamp = time();

  // Get the date from the timestamp.
  $getDate = date('d-m-Y', $timestamp);

  // Get today's date in 6:00.
  $startDate = strtotime($getDate . '06:00');

  // The today's date one hour earlier.
  $endDate = $timestamp - 3600;

  // Get a random timestamp.
  $randomDate = rand($startDate, $endDate);

  // Return the random timestamp.
  return $randomDate;

}

}