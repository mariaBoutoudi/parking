<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The departure form.
 */
class DepartureFormSearch extends FormBase {

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
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    // Here we set a unique form id
    // Same name with the routing name.
    return 'parking_departure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // The unique id of the vehicle.
    $form['vehicle_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Vehicle ID'),
      '#required' => TRUE,
    ];

    // Submit button.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the car id from its form fiels.
    $vehicleId = $form_state->getValue('vehicle_id');

    // Load entity type manager service.
    $entityManager = $this->entityTypeManager;

    // Load node by its title.
    $properties = ['title' => $vehicleId];
    $nodeEntity = $entityManager->getStorage('node')->loadByProperties($properties);

    // If we already have a node saved.
    if ($nodeEntity) {

      // Redirect to routing 'parking_departure_form'.
      // Path: '/departureform/book_id'.
      $form_state->setRedirect('parking_departure_form', ['book_id' => $form_state->getValue('vehicle_id')]);
    }

    // If the vehicle id does not exist.
    else {

      // Show a message.
      \Drupal::messenger()->addError($this->t("This vehicle id does not exist."));

      // Redirect to search form.
      $form_state->setRedirect('parking_departure_form');
    }
  
  }

}
