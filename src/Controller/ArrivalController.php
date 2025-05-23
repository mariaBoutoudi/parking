<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * The arrival controller.
 */
class ArrivalController extends ControllerBase {

/**
 * Return the arrival form.
 * 
 * @return array
 */
public function content() {

   // Get the arrival form via controller.
        $arrivalForm['arrival_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\ArrivalForm');

    return [
        // The theme hook name.
        '#theme' => 'arrival_template',
        // The variables.
        '#arrivalform' => $arrivalForm,
    ];
  
}
  
}