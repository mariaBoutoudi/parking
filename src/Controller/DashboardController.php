<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class DashboardController extends ControllerBase {

  /**
   * Returns a renderable array for a test page.
   *
   * return []
   */
  public function content() {

    // // Get the arrival form via controller.
    // $dashboardpage['dashboard_page'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\ArrivalForm');

    return [
        // Your theme hook name.
        '#theme' => 'dashboard_template',
        // // Your variables.
        '#currentdate' => date('d-m-Y'),
        '#arrivalbutton' => 'Arrival',
        '#departurebutton' => 'Departure',
        '#carlist' => 'List with cars'
    ];
  }

}