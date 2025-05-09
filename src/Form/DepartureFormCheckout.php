<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\parking\Controller\ConstantsController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The departure form.
 */
class DepartureFormCheckout extends FormBase {

  /**
   * The entitytypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entitytypeManager.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    // Here we set a unique form id
    // Same name with the routing name.
    return 'parking_departure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Set a value for the hidden form field 'what'.
    // $what = 'checkout';

    // Set a value to the checkout time cause is NULL in the arrival form.
    // $currentTime = time();

    // If we already have a car id in the url.
    // if ($book_id) {

      // Load the node by its id from the url.
      // $entityManager = $this->entityTypeManager;
      // $properties = ['title' => $book_id];
      // $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

      // Get from the array [nodeid => {nodeObject}] the nodeObject.
      // $node = reset($nodeEntity);

      // In case the node exists.
      // if ($nodeEntity) {

      //   // Have the car id form field as hidden to be used.
        // $form['car_id'] = [
        //   '#type' => 'hidden',
        //   '#value' => $node->id(),
        // ];

        // Get the plate number from the node to be shown.
        // the message with the car plate.
        // $plateNum = 'Plate number: ' . $node->get('field_car_plate')->value;

        // Get the cost from the node to be shown.
        // Call the function that gives the total cost.
        // With the datetime in and datetime out.
        // Which is declared at the begining of buildForm.
        // $cost = $this->calculateCost($node->get('field_datetime_in')->value, $currentTime);

        // The message with the total cost.
        // $costMessage = 'Total payment: ' . $cost . ' euros';

        // $form['message'] = [
        //   '#type' => 'markup',
        //   '#markup' => '<div id="information">The outgoing car with ' . $book_id . ' ID has the following entry:</div>',

        // ];

        // This field is used for the node elements to be shown.
        // $form['info'] = [
        //   '#type' => 'markup',
        //   '#markup' => '<div id="information"> ' . $plateNum . '<br>' . $costMessage . '</div>',

        // ];

        // $form['pay_message'] = [
        //   '#type' => 'markup',
        //   '#markup' => '<div id="information">Please check Payment only if the outgoing car has paid ' . $cost . ' euros.</div>',

        // ];
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

      

      // If there is no node entity.
      // else {

      //   // The unique id of the car.
      //   $form['car_id'] = [
      //     '#type' => 'number',
      //     '#title' => $this->t('Car ID'),
      //     '#required' => TRUE,
      //   ];

      //   // Submit button.
      //   $form['actions']['submit'] = [
      //     '#type' => 'submit',
      //     '#value' => $this->t('Search'),
      //     '#button_type' => 'primary',
      //   ];

      // }
    

    // If book id (in url) does not exist.
    // else {

    //   $form['info'] = [
    //     '#type' => 'markup',
    //     '#markup' => '<div id="information">Please enter the ID of the outgoing car.</div>',

    //   ];
    //   // The unique id of the car.
    //   $form['car_id'] = [
    //     '#type' => 'number',
    //     '#title' => $this->t('Car ID'),
    //     '#required' => TRUE,
    //   ];

    //   // Submit button.
    //   $form['actions']['submit'] = [
    //     '#type' => 'submit',
    //     '#value' => $this->t('Search'),
    //     '#button_type' => 'primary',
    //   ];
    //   $what = 'search';

  

    // A hidden field to be used.
    // $what has two values.
    // Search value if the user is in search form (button).
    // Checkout value if the user is in checkout form (button).
    // We set the value to be used in submitForm function.
    // $form['what'] = [
    //   '#type' => 'hidden',
    //   '#default_value' => $what,
    // ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the car id from its form fiels.
    $carId = $form_state->getValue('car_id');

    // Load entity type manager service.
    $entityManager = $this->entityTypeManager;

    // Load node by its title.
    $properties = ['title' => $carId];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

    // If the user is in form with checkout button.
    // if ($form_state->getValue('what') == 'checkout') {

      $node = $entityManager->getStorage('node')->load($carId);
      $in = $node->get('field_datetime_in')->value;
      $out = time();
      $cost = $this->calculateCost($in, $out);

      // Update the specific node with the values of the form fields.
      $update_node = Node::load($carId);
      $timestamp = time();
      $update_node->set('field_datetime_out', $timestamp);
      $update_node->set('field_payment', $form_state->getValue('payment'));
      $update_node->set('field_cost', $cost);
      $update_node->save();

      // Show a message.
      \Drupal::messenger()->addMessage($this->t("Successful registration."));

    // }

    // else {

    //   // If we already have a node saved.
      if ($nodeEntity) {

    //     // Redirect to routing 'parking_departure_form'.
    //     // Path: '/departureform/book_id'.
        $form_state->setRedirect('parking_departure_form', ['book_id' => $form_state->getValue('car_id')]);
      }

    //   // If the Car id does not exist.
      else {

        // Show a message.
        \Drupal::messenger()->addError($this->t("This car id does not exist."));

        // Redirect to search form.
        $form_state->setRedirect('parking_departure_form');
      }
    
    }
  

  /**
   * Calculate the cost that car must pay.
   *
   * @param string $checkIn
   *   The arrival time.
   * @param string $checkOut
   *   The departure time.
   *
   * @return string
   *   The final cost.
   */
  public function calculateCost($checkIn, $checkOut) {

    // The cost for the first hour.
    // Comes from the CONSTANTS in the controller.
    $firstHour = ConstantsController::FIRST_HOUR;

    // The cost for each hour.
    // Comes from the CONSTANTS in the controller.
    $pricePerHour = ConstantsController::PRICE_PER_HOUR;

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
