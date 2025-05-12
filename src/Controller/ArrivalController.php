<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class ArrivalController extends ControllerBase {

/**
 * @return [type]
 */
public function content() {

   // Get the arrival form via controller.
        $arrivalForm['arrival_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\ArrivalForm');

    return [
        // Your theme hook name.
        '#theme' => 'arrival_template',
        // // Your variables.
        '#arrivalform' => $arrivalForm,
    ];
  
}
  
}