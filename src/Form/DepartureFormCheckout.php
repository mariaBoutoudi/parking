<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\parking\Services\ParkingService;
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
   * The calculator.
   *
   * @var \Drupal\parking\Services\ParkingService
   */
  protected $calculator;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entitytypeManager.
   * @param \Drupal\parking\Services\ParkingService $calculator
   *   The calculator
   */
  public function __construct(EntityTypeManager $entityTypeManager, ParkingService $calculator) {
    $this->entityTypeManager = $entityTypeManager;
    $this->calculator = $calculator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('parking.calculation')
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
  public function buildForm(array $form, FormStateInterface $form_state, $book_id = NULL) {

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

    // Insert the value of $book_id in form_state.
    // We get the value of $book_id from the controller 
    // and set it as a parameter in buildForm.
    $form_state->set('bookId', $book_id);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


    // Get the bookid.
    // We set the value in build form.
    $id = $form_state->get('bookId');

    // Load entity type manager service.
    $entityManager = $this->entityTypeManager;

    // Load node by its title as an array.
    $properties = ['title' => $id];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

    // Get nodes with title $id as an array [key=>value].
    // With reset() get the value of the first node of the array.
    $node = reset($nodeEntity);

    // Get the id of the node array.
    $nid = $node->id();

    // Get the time the car came in parking.  
    $in = $node->get('field_datetime_in')->value;

    // Set the departure time of the car.
    $out = time();

    // Call the function which gives the total cost.
    $cost = $this->calculator->calculateCost($in, $out);

 

    // Update the specific node with the values of the form fields.
    $update_node = Node::load($nid);
    $timestamp = time();
    $update_node->set('field_datetime_out', $timestamp);
    $update_node->set('field_payment', $form_state->getValue('payment'));
    $update_node->set('field_cost', $cost);
    $update_node->save();

    // Show a message.
    \Drupal::messenger()->addMessage($this->t("Successful registration."));

      // If we already have a node saved.
      if ($nodeEntity) {

       // Redirect to routing 'parking_dashboard'.
        $form_state->setRedirect('parking_dashboard');
      }

    //   // If the Car id does not exist.
      else {

        // Show a message.
        \Drupal::messenger()->addError($this->t("This car id does not exist."));

        // Redirect to search form.
        $form_state->setRedirect('parking_departure_form');
      }
    
    }

}
