<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\parking\Controller\ConstantsController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

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
    // We get the value of $book_id from the controller.
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

    // Load node by its title.
    $properties = ['title' => $id];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);
    $nodeEntity_2 = $entityManager->getStorage('node')->load('96');
    $nodeEntity_3 = Node::load('96');
    
    // $node = reset($nodeEntity);

    $in = $node->get('field_datetime_in')->value;
    $out = time();
    $cost = $this->calculateCost($in, $out);

 

    // Update the specific node with the values of the form fields.
    $update_node = Node::load($id);
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
