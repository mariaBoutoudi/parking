<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Services\ParkingService;

/**
 * An departure controller.
 */
class DepartureController extends ControllerBase {

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
    return new self(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('parking.calculation')
    );
  }  

/**
 * @param null $book_id
 * 
 * @return array
 */
public function content($book_id = NULL) {

  // Set variables to be used.
  $currentTime = time();
  $node = '';
  $plateNum = '';
  $cost = '';
  
  //  If we already have a vehicle id in the url.
   if ($book_id) {

    // Load the node by its id from the url.
    $entityManager = $this->entityTypeManager;
    $properties = ['title' => $book_id];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

    // Get from the array [nodeid => {nodeObject}] the nodeObject.
    $node = reset($nodeEntity);

    // Get from the node entity the value of the vehicle plate.
    $plateNum = $node->get('field_car_plate')->value;

    // Get from the node entity the value of th time type.
    $timeType = $node->get('field_time_type')->value;

    // If the type of time is per hour.
    if($timeType == 'per_hour'){

      // Calculate the cost via ParkingService.
      $cost = $this->calculator->calculateCostPerHour($node->get('field_datetime_in')->value, $currentTime);
    }
    // In case the time type is per day.
    else{

      // Calculate the cost via ParkingService.
      $cost = $this->calculator->calculateCostPerDay($node->get('field_datetime_in')->value, $currentTime);
    }
  
    // In case the node exists.
    if ($nodeEntity) {

  // Get the departure form via controller.
  // Set $book_id as a parameter to be used in checkout form.
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
        // The theme hook name.
        '#theme' => 'departure_template',
        // The variables.
        '#bookid' => $book_id ,
        '#plateNum' => $plateNum,
        '#cost' => $cost,
        '#departureform' => $departureForm,
    ];
  
}
  
}