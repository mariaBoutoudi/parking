<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Services\ParkingService;


/**
 * An example controller.
 */
class DashboardController extends ControllerBase {

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
   * Returns a renderable array for a test page.
   *
   * return []
   */
  public function content() {

    $totalPositions = '100';
    $occupiedPositions = $this->calculator->getSpecificParkingNodes();

    // // Get the available car positions.
    $availablePositions = $totalPositions - $occupiedPositions;

    return [
        // Your theme hook name.
        '#theme' => 'dashboard_template',
        // // Your variables.
        '#currentdate' => date('d-m-Y'),
        '#arrivalbutton' => 'Arrival Form',
        '#departurebutton' => 'Departure Form',
        '#occupiedpositions' => $occupiedPositions,
        '#availablepositions' => $availablePositions,
        '#carlist' => 'Car list',
        '#cache' => ['max-age' => 0,
  ],
    ];
  }

}