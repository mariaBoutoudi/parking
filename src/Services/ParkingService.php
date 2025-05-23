<?php

namespace Drupal\parking\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * The ParkingService calculator class.
 */
 class ParkingService {

  /**
   * The entitytypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(EntityTypeManager $entityTypeManager, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }
 
 /**
   * Calculate the cost that vehicle must pay per hour.
   *
   * @param string $checkIn
   *   The arrival time.
   * @param string $checkOut
   *   The departure time.
   *
   * @return string
   *   The final cost.
   */
  public function calculateCostPerHour($checkIn, $checkOut) {

    // The cost for the first hour.
    // from the config form.
    $firstHour = $this->configFactory->get('parking.config.form')->get('first_hour');

    // The cost for each hour.
    // from the config form.
    $pricePerHour = $this->configFactory->get('parking.config.form')->get('per_hour');

    // Calculate the time the vehicle was in parking.
    // The values are in timestamp.
    $seconds = $checkOut - $checkIn;

    // Convert the timestamp seconds in hours.
    $hours = $seconds / 3600;

    // Round up the hours.
    // Extract the first hour cause the cost is different.
    // Multiply the hours left with the price per hour.
    // Add to final cost (in euros) the first hour price.
    $cost = ((ceil($hours) - 1) * $pricePerHour) + $firstHour;
    return $cost;

  }

  
 /**
   * Calculate the cost that vehicle must pay per day.
   *
   * @param string $checkIn
   *   The arrival time.
   * @param string $checkOut
   *   The departure time.
   *
   * @return string
   *   The final cost.
   */
  public function calculateCostPerDay($checkIn, $checkOut){

    // The cost for the first day.
    // Comes from the config form.
    $pricePerDay = $this->configFactory->get('parking.config.form')->get('per_day');

    // Calculate the time the vehicle was in parking in seconds.
    // The values are in timestamp.
    $parkingInSeconds = $checkOut - $checkIn;

    // Convert the timestamp seconds in days.
    $daySeconds = 24 * 3600;
    $days = $parkingInSeconds / $daySeconds;

    // Round up the days.
    // Multiply the days with the price per day.
    $cost = ceil($days) * $pricePerDay;
    return $cost;


  }

  /**
   * @return string
   */
  public function getSpecificParkingNodes() {

    // Get the nodes.
   $query = $this->entityTypeManager->getStorage('node')->getQuery();

  //  Get parking nodes id with the specific fields.
   $nodes = $query
      ->condition('type', 'parking')
      ->condition('status', 1)
      ->condition('field_payment', '1', '<>')
      ->condition('field_datetime_out', NULL, 'IS NULL')
      ->accessCheck(FALSE)
      ->execute();

  // Load the filtered nodes by their id.
  $parkingNodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nodes);

  // Get the number of vehicles in the parking.
  $occupiedSpaces = count($parkingNodes);

  return $occupiedSpaces;

  }

public function getCarsWithNoPayment(){

    // Get the nodes.
   $query = $this->entityTypeManager->getStorage('node')->getQuery();

  //  Get parking nodes id with the specific fields.
   $nodes = $query
      ->condition('type', 'parking')
      ->condition('status', 1)
      ->condition('field_payment', '1', '<>')
      ->condition('field_datetime_out', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE)
      ->execute();

    // Load the specific nodes
    $vehicleNoPay = $this->entityTypeManager->getStorage('node')->loadMultiple($nodes);

    // Get the number of vehicles that did not pay.
    $vehicles = count($vehicleNoPay);

    return $vehicles;

}
 }

