<?php

namespace Drupal\generate_vehicles\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * The generate vehicles controller.
 */
class GenerateVehiclesController extends ControllerBase {

  /**
   * Return the generate form.
   *
   * @return array
   *   Render array with values.
   */
  public function content() {

    // Get the arrival form via controller.
    $generateVehiclesForm['generate_vehicles_form'] = \Drupal::formBuilder()->getForm('Drupal\generate_vehicles\Form\GenerateVehiclesForm');

    return [
        // The theme hook name.
      '#theme' => 'generate_vehicles_template',
        // The variables.
      '#generatevehicles' => $generateVehiclesForm,
    ];

  }

}
