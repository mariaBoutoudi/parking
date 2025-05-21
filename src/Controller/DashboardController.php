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
    $totalSpaces = $this->configFactory->get('parking.config.form')->get('total_spaces');
    $occupiedSpaces = $this->calculator->getSpecificParkingNodes();
    $chargePerHour = $this->configFactory->get('parking.config.form')->get('per_hour');
    $FirstHourCharge = $this->configFactory->get('parking.config.form')->get('first_hour');
    $unpaidtickets = $this->calculator->getCarsWithNoPayment();

    // Get the available car positions.
    $availableSpaces = $totalSpaces - $occupiedSpaces;

    // Get route from view car-list.
    $urlList = Url::fromRoute('view.car_list.page_carlist');
    $carListUri = $urlList->toString();

    // Get route from view debtors.
    $urlDebtors = Url::fromRoute('view.car_list.page_debtors');
    $debtorsUri = $urlDebtors->toString();

    // Get route from config parking settings.
    $configFormUrl = Url::fromRoute('parking_config_form');
    $configUri = $configFormUrl->toString();


    return [
        // Your theme hook name.
        '#theme' => 'dashboard_template',
        // Your variables.
        '#currentdate' => date('d-m-Y'),
        '#arrivalbutton' => 'Add New Vehicle',
        '#departurebutton' => 'Check Out Vehicle',
        '#occupiedspaces' => $occupiedSpaces,
        '#availablespaces' => $availableSpaces,
        '#carlist' => 'Vehicles list',
        '#perhour' => $chargePerHour,
        '#firsthour' => $FirstHourCharge,
        '#unpaidtickets' => $unpaidtickets,
        '#debtorsview' => 'See More',
        '#reservation' => 'Reservation for parking spot',
        '#urlcarlist' => $carListUri,
        '#urldebtors' => $debtorsUri,
        '#configform' => $configUri,
        '#change' => "Change",
        '#cache' => ['max-age' => 0,
],
    ];
  }

}