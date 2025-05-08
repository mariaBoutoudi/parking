<?php

namespace Drupal\parking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\parking\Form\DepartureForm;


/**
 * An example controller.
 */
class DepartureController extends ControllerBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new self(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
    );
  }  

public function content() {

  // TO DO----------->condition on the 2 different departure forms.
  // Get the departure form via controller.
  $departureForm['departure_form'] = \Drupal::formBuilder()->getForm('Drupal\parking\Form\DepartureForm');
  return $departureForm;

    return [
        // Your theme hook name.
        '#theme' => 'departure_template',
        // Your variables.
        // '#bookid' => ,
        '#plateNum' => 'maria',
        // $node->get('field_car_plate')->value,
        // '#cost' => $this->calculateCost($node->get('field_datetime_in')->value, $currentTime),


    ];
    }

}