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
    if ($numOfVehicles > 20 || $numOfVehicles == 0) {
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

    // Split total nodes into two parts.
    // Some nodes for current day (2/3).
    // Some nodes for previous days (1/3).
    // Nodes for previous day.
    $previousDay = $numOfVehicles / 3;

    // Nodes for current day.
    $currentDay = $numOfVehicles - $previousDay;

    // Set a variable for the batch.
    $operations = [];

    // Loop through the number of vehicles.
    // Split numbers in current and previous day.
    for ($i = 1; $i <= $numOfVehicles; $i++) {

      // Set the vehicle cost as NULL.
      $nodeCost = NULL;

      // Handle entities for current day.
      if ($i <= $currentDay) {

        // Set charge type per hour.
        $nodeTimeType = 'per_hour';

        // Generate a timestamp randomly for arrival vehicle for current day.
        $nodeDateIn = $this->generator->generateRandomDate();

        // Generate a timestamp randomly for departure vehicle.
        // Could be a timestamp or NULL.
        $nodeDateOut = $this->generator->generateRandomDateOut($nodeDateIn);

        // If the vehicle has left the parking.
        if ($nodeDateOut != NULL) {

          // Get random payment value.
          $nodePayment = random_int(0, 1);

          // Generate vehicle cost with booking per hour.
          $nodeCost = $this->calculator->calculateCostPerHour($nodeDateIn, $nodeDateOut);
        }

        // If the vehicle is still in the parking.
        else {

          // The payment checkbox field is 'No'.
          $nodePayment = 0;
        }
      }
      // Handle entities for previous day.
      else {

        // Set charge type per day.
        $nodeTimeType = 'per_day';

        // Generate a timestamp randomly for arrival vehicle for previous day.
        $nodeDateIn = $this->generator->generateRandomDate(FALSE);

        // Set checkout date as NULL.
        // The vehicles with per day booking won't have a checkout.
        $nodeDateOut = NULL;

        // And the payment will be 'No'.
        $nodePayment = 0;
      }

      // Generate a vehicle plate randomly.
      $nodePlate = $this->generator->generateRandomVehiclePlates();

      // Create an array with the node fields.
      $vehicleValues = [
        'title' => $nodeDateIn,
        'field_car_plate' => $nodePlate,
        'field_datetime_in' => $nodeDateIn,
        'field_datetime_out' => $nodeDateOut,
        'field_time_type' => $nodeTimeType,
        'field_payment' => $nodePayment,
        'field_cost' => $nodeCost,

      ];

      // Generate the nodes after submission.
      // Using the createBatchNode.
      $operations[] = ['\Drupal\generate_vehicles\Form\GenerateVehiclesForm::createBatchNode', [$vehicleValues]];

    }

    // Create an array with the batch fields.
    $batch = [
      'title' => $this->t('Generate Vehicles'),
      'init_message' => $this->t('Starting to process vehicles.'),
      'progress_message' => $this->t('Completed @current out of @total nodes.'),
      'finished' => '\Drupal\generate_vehicles\Form\GenerateVehiclesForm::finishedGeneration',
      'error_message' => $this->t('Event processing has encountered an error.'),
      'operations' => $operations,
    ];

    // Add the new batch set.
    batch_set($batch);

    // Redirect to dashboard page.
    $form_state->setRedirect('parking_dashboard');

  }

  /**
   * Message after the end of generation.
   *
   * @param bool $success
   *   The process of generation.
   * @param array $results
   *   The vehicles been generated.
   * @param array $operations
   *   The operations.
   */
  public static function finishedGeneration($success, $results, $operations) {

    // If the generation is processed with no error.
    if ($success) {

      // Print a message.
      $message = \Drupal::translation()->formatPlural(
      count($results),
      'One vehicle processed.', '@count vehicles processed.'
      );

    }

    // If there is an error in generation.
    else {

      // Print a message.
      $message = t('Finished with an error.');
    }

    // Add message.
    \Drupal::messenger()->addMessage($message);
  }

  /**
   * Create the vehicle nodes.
   *
   * @param array $values
   *   The node fields.
   * @param array $context
   *   The context.
   */
  public static function createBatchNode(array $values, &$context) {
    sleep(3);
    // Create node of type parking.
    $new_node = Node::create(['type' => 'parking']);

    // Loop through each field and value of node.
    foreach ($values as $field => $value) {

      // Set each field and value.
      $new_node->set($field, $value);
    }
    // Save the vehicle node.
    $new_node->enforceIsNew();
    $new_node->save();

    // Print a message with the title of the node after generation.
    $context['message'] = 'Generate nodes with title: ' . $values['title'];
    $context['results'][] = $values['title'];

  }

}
