<?php

namespace Drupal\generate_vehicles\Services;
use Drupal\node\Entity\Node;

/**
 * The GenerateService class.
 */
class GenerateService {

  /**
   * Generate random vehicle plates.
   *
   * @return string
   *   The vehicle plate.
   */
  public function generateRandomVehiclePlates() {

    // The letters for the vehicle plate.
    $letters = 'ABEHIKMNOPTXYZ';

    // The length of $letters string.
    $lettersLength = strlen($letters);

    // Set a value for the random letters.
    $randomLetters = '';

    // Loop through the letters.
    // Choose random.
    for ($i = 0; $i < 3; $i++) {
      $randomLetters .= $letters[random_int(0, $lettersLength - 1)];
    }

    // The numbers for the vehicle plate.
    $numbers = '0123456789';

    // The length of $numbers string.
    $numbersLength = strlen($numbers);

    // Set a value for the random numbers.
    $randomNumbers = '';

    // Loop through the numbers.
    // Choose random.
    for ($j = 0; $j < 4; $j++) {
      $randomNumbers .= $numbers[random_int(0, $numbersLength - 1)];
    }

    // The final random vehicle plate.
    $vehiclePlate = $randomLetters . $randomNumbers;

    // Return the vehicle plate.
    return $vehiclePlate;

  }

  /**
   * Generate random date.
   *
   * @param bool $today
   *   The reference to the current day.
   *
   * @return string
   *   The date.
   */
  public function generateRandomDate($today = TRUE) {

    // Current time.
    $timestamp = time();

    // The reference to current day is TRUE.
    if ($today) {

      // Get only the date from the timestamp.
      $getDate = date('d-m-Y', $timestamp);

      // Get today's date in 6:00.
      $startDate = strtotime($getDate . '06:00');

      // The today's date one hour earlier.
      // Set as departure time.
      $endDate = $timestamp - 3600;

    }
    // The reference to current day is FALSE.
    else {

      // Set a variable to prenious day.
      $yesterday = $timestamp - (3600 * 24);

      // Get only the date from the timestamp.
      $getDate = date('d-m-Y', $yesterday);

      // Set as start date 6:00 yesterday.
      $startDate = strtotime($getDate . '06:00');

      // Set as end date 22:00 yesterday.
      $endDate = strtotime($getDate . '22:00');

    }

    // Get a random timestamp.
    $randomDate = rand($startDate, $endDate);

    // Return the random timestamp.
    return $randomDate;

  }

  /**
   * Generate random the vehicle departure.
   *
   * @param string $dateTimeIn
   *   The arrival time.
   *
   * @return string
   *   Checkout time.
   */
  public function generateRandomDateOut($dateTimeIn) {

    // The time vehicle departures.
    // Two hours after arrival.
    $dateTimeOut = $dateTimeIn + (3600 * 2);

    // Get random value for departure time.
    // Either NULL or 2 hours after arrival.
    $departureValues = [NULL, $dateTimeOut];

    // Get the key of the array randomly.
    $randTimeKey = random_int(0, 1);

    // Set a variable for the random checkout.
    $randomDateOut = $departureValues[$randTimeKey];

    // Return the vehicle departure time.
    return $randomDateOut;
  }

  /**
   * Create the vehicle nodes.
   *
   * @param array $values
   *   The node fields.
   * @param array $context
   *   The context.
   */  
  public static function createBatchNode(array $values, &$context) {

    // Create node of type parking.
    $new_node = Node::create(['type' => 'parking']);

    // Loop through each field and value of node.
    foreach ($values as $field => $value) {

      // Set each field and value.
      $new_node->set($field, $value);
    }
    // Save the vehicle node.
    $new_node->enforceIsNew();
    $new_node->save();

    // Print a message with the title of the node after generation.
    $context['message'] = 'Generate nodes with title: ' . $values['title'];
    $context['results'][] = $values['title'];

  }


  public function createNodes() {

  }

  
}
