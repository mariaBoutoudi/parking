<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Services\CalculateCostService;

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
   * The calculator.
   *
   * @var \Drupal\parking\Services\CalculateCostService
   */
  protected $calculator;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entitytypeManager.
   * @param \Drupal\parking\Services\CalculateCostService $calculator
   *   The calculator
   */
  public function __construct(EntityTypeManager $entityTypeManager, CalculateCostService $calculator) {
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
    $cost = $this->calculator->calculateCost($node->get('field_datetime_in')->value, $currentTime);

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
  
}