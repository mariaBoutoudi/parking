<?php

namespace Drupal\generate_vehicles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\parking\Services\ParkingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generate_vehicles\Services\GenerateService;

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
   * The generator.
   *
   * @var \Drupal\generate_vehicles\Services\GenerateService
   */
  protected $generator;

  
  /**
   * The constructor.
   *
   * @param \Drupal\parking\Services\ParkingService $calculator
   *   The calculator.
   * 
   * @param \Drupal\generate_vehicles\Services\GenerateService $generator
   *   The generator.
   */
  public function __construct(ParkingService $calculator, GenerateService $generator) {
    $this->calculator = $calculator;
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('parking.calculation'),
      $container->get('vehicles.generation')
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
    
    for($i = 1; $i <= $numOfVehicles; $i++){
      $nodeCost = NULL;
      if($i<=$currentDay) {
        // nodes for the current day.
        $nodeTimeType = 'per_hour';
        $nodeDateIn = $this->generator->generateRandomDate();
        $nodeDateOut = $this->generator->generateRandomDateOut($nodeDateIn);

        if($nodeDateOut != NULL){
            // Get random payment value.
           $nodePayment = random_int(0, 1);
          // Service for calculating the cost per hour.
          $nodeCost = $this->calculator->calculateCostPerHour($nodeDateIn, $nodeDateOut);
        }
        else{
           $nodePayment = 0;
        }
      }
      else{
        // nodes for previous day.
        $nodeTimeType = 'per_day';
        $nodeDateIn = $this->generator->generateRandomDate(FALSE);
        $nodeDateOut = NULL;
        $nodePayment = 0;
      }

    $nodePlate = $this->generator->generateRandomVehiclePlates();
    // Generate the nodes after submission.
    $new_node = Node::create(['type' => 'parking']);
    $new_node->set('title', $nodeDateIn);
    $new_node->set('field_car_plate', $nodePlate);
    $new_node->set('field_datetime_in', $nodeDateIn);
    $new_node->set('field_datetime_out', $nodeDateOut);
    $new_node->set('field_time_type', $nodeTimeType);
    $new_node->set('field_payment', $nodePayment);
    $new_node->set('field_cost', $nodeCost);
    $new_node->enforceIsNew();
    $new_node->save();

    }
    // A message shown after the submission.
    \Drupal::messenger()->addMessage($this->t('@total number of vehicles has been created.', ['@total' => $numOfVehicles]));

    // Redirect to dashboard page.
    $form_state->setRedirect('parking_dashboard');

  }

}