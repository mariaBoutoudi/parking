<?php

namespace Drupal\generate_vehicles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
      $container->get('generate_vehicles.generation')
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

    // Get from generator the array of nodes.
    $nodesArray = $this->generator->generateNodesArray($numOfVehicles);

    // Loop through each array.
    foreach($nodesArray as $node){

      // Create after submission.
      // Using the batch operators.
      $operations[] = ['\Drupal\generate_vehicles\Services\GenerateService::createBatchNode', [$node]];
    }
  

    // Create and process the batch operations.
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

}
