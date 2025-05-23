<?php

namespace Drupal\parking\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The config class for the module.
 */
class ParkingConfigForm extends ConfigFormBase {

 /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    // The unique formId.
    return 'parking_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['parking.config.form'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    // Create form fields in our configuration form.
    $form['first_hour'] = [
      '#type' => 'number',
      '#title' => $this->t('First Hour'),
      '#description' => $this->t('The cost for the first hour'),
      '#default_value' => $this->config('parking.config.form')->get('first_hour'),
    ];

    $form['per_hour'] = [
      '#type' => 'number',
      '#title' => $this->t('Per Hour'),
      '#description' => $this->t('The cost per hour'),
      '#default_value' => $this->config('parking.config.form')->get('per_hour'),
     
    ];

    $form['per_day'] = [
      '#type' => 'number',
      '#title' => $this->t('Per Day'),
      '#description' => $this->t('The cost per day'),
      '#default_value' => $this->config('parking.config.form')->get('per_day'),
  
    ];

    $form['total_spaces'] = [
      '#type' => 'number',
      '#title' => $this->t('Total Parking Spaces'),
      '#description' => $this->t('Total spaces of the parking'),
      '#default_value' => $this->config('parking.config.form')->get('total_spaces'),

    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('parking.config.form')
    // When the submit button is pressed the form fields
    // will have the last values the user typed.
      ->set('first_hour', $form_state->getValue('first_hour'))
      ->set('per_hour', $form_state->getValue('per_hour'))
      ->set('per_day', $form_state->getValue('per_day'))
      ->set('total_spaces', $form_state->getValue('total_spaces'))
      ->save();
    parent::submitForm($form, $form_state);
  }
 

}
