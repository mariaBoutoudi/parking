<?php

namespace Drupal\parking\Services;

use Drupal\parking\ConstantsController;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * The ParkingService calcutator class.
 */
 class ParkingService {

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

  /**
   * @return [type]
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

  // Get the number of cars in the parking.
  $occupiedPositions = count($parkingNodes);

  return $occupiedPositions;

  }
 }

