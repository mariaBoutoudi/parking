<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Services\ParkingService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;


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
   * Configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entitytypeManager.
   * @param \Drupal\parking\Services\ParkingService $calculator
   *   The calculator
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(EntityTypeManager $entityTypeManager, ParkingService $calculator, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->calculator = $calculator;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('parking.calculation'),
      $container->get('config.factory'),

      
    );
  }  

  /**
   * Returns a renderable array for a test page.
   *
   * return []
   */
  public function content() {

    // Get values to be used in twig template.
    $totalPositions = $this->configFactory->get('parking.config.form')->get('total_positions');
    $occupiedPositions = $this->calculator->getSpecificParkingNodes();
    $chargePerHour = $this->configFactory->get('parking.config.form')->get('per_hour');
    $FirstHourCharge = $this->configFactory->get('parking.config.form')->get('first_hour');
    $carsNotPaid = $this->calculator->getCarsWithNoPayment();

    // Get the available car positions.
    $availablePositions = $totalPositions - $occupiedPositions;

    // Get route from view car-list.
    $url = Url::fromRoute('view.car_list.page_1');
    $carListUri = $url->toString();

    return [
        // Your theme hook name.
        '#theme' => 'dashboard_template',
        // Your variables.
        '#currentdate' => date('d-m-Y'),
        '#arrivalbutton' => 'Arrival Form',
        '#departurebutton' => 'Departure Form',
        '#occupiedpositions' => $occupiedPositions,
        '#availablepositions' => $availablePositions,
        '#carlist' => 'Car list',
        '#perhour' => $chargePerHour,
        '#firsthour' => $FirstHourCharge,
        '#carsnotpaid' => $carsNotPaid,
        '#debts' => 'See More',
        '#reservation' => 'Reservation for parking spot',
        '#urlcarlist' => $carListUri,
        '#cache' => ['max-age' => 0,
],
    ];
  }

}