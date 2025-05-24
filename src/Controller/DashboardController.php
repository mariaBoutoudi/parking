<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Services\ParkingService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;

/**
 * The dashboard controller.
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
   *   The calculator.
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
   * Returns a renderable array for a dashboard page.
   *
   * @return array
   *   Render array with values.
   */
  public function content() {

    // Get values from the config form to be used in twig template.
    $totalSpaces = $this->configFactory->get('parking.config.form')->get('total_spaces');
    $occupiedSpaces = $this->calculator->getSpecificParkingNodes();
    $chargePerHour = $this->configFactory->get('parking.config.form')->get('per_hour');
    $firstHourCharge = $this->configFactory->get('parking.config.form')->get('first_hour');
    $unpaidtickets = $this->calculator->getVehiclesWithNoPayment();
    $chargePerDay = $this->configFactory->get('parking.config.form')->get('per_day');

    // Get the available vehicle positions.
    $availableSpaces = $totalSpaces - $occupiedSpaces;

    // Get route from view 'car-list'.
    $urlList = Url::fromRoute('view.car_list.page_vehiclelist');
    $carListUri = $urlList->toString();

    // Get route from view 'debtors'.
    $urlDebtors = Url::fromRoute('view.car_list.page_debtors');
    $debtorsUri = $urlDebtors->toString();

    // Get route from config parking settings.
    $configFormUrl = Url::fromRoute('parking_config_form');
    $configUri = $configFormUrl->toString();

    return [
        // The theme hook name.
      '#theme' => 'dashboard_template',
        // The variables.
      '#currentdate' => date('d-m-Y'),
      '#arrivalbutton' => 'Add New Vehicle',
      '#departurebutton' => 'Check Out Vehicle',
      '#occupiedspaces' => $occupiedSpaces,
      '#availablespaces' => $availableSpaces,
      '#vehiclelist' => 'Vehicles list',
      '#perhour' => $chargePerHour,
      '#perday' => $chargePerDay,
      '#firsthour' => $firstHourCharge,
      '#unpaidtickets' => $unpaidtickets,
      '#debtorsview' => 'See More',
      '#urlcarlist' => $carListUri,
      '#urldebtors' => $debtorsUri,
      '#configform' => $configUri,
      '#change' => "Change",
      '#cache' => ['max-age' => 0,
      ],
    ];
  }

}
