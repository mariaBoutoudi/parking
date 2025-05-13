<?php

namespace Drupal\parking\Services;

use Drupal\parking\ConstantsController;

/**
 * The CostService calcutator class.
 */
 class CostService {
 
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

