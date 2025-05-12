<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An example controller.
 */
class DepartureController extends ControllerBase {

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
    return new self(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
    );
  }  

public function content($book_id = NULL) {

  $currentTime = time();
  $node = '';
  $plateNum = '';
  $cost = '';
  

  //  If we already have a car id in the url.
   if ($book_id) {

    // Load the node by its id from the url.
    $entityManager = $this->entityTypeManager;
    $properties = ['title' => $book_id];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

    // // Get from the array [nodeid => {nodeObject}] the nodeObject.
    $node = reset($nodeEntity);

    $plateNum = $node->get('field_car_plate')->value;
    $cost = $this->calculateCost($node->get('field_datetime_in')->value, $currentTime);

    // In case the node exists.
    if ($nodeEntity) {

  // Get the departure form via controller.
  // Put $book_id to be used in checkout form.
  $departureForm['departure_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\DepartureFormCheckout', $book_id);
    }

    // If there is no node.
    else {
        // Get the departure form via controller.
  $departureForm['departure_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\DepartureFormSearch');
    }
   }

  //  In case $bookid is NULL.
  else {
        // Get the departure form via controller.
        $departureForm['departure_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\DepartureFormSearch');
    }
  

    return [
        // Your theme hook name.
        '#theme' => 'departure_template',
        // // Your variables.
        '#bookid' => $book_id ,
        '#plateNum' => $plateNum,
        '#cost' => $cost,
        '#departureform' => $departureForm,
    ];
  
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