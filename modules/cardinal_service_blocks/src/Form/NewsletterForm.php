<?php

namespace Drupal\cardinal_service_blocks\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NewsletterForm.
 *
 * @package Drupal\cardinal_service_blocks\Form
 */
class NewsletterForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'cardinal_service_blocks_newsletter';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['u'] = [
      '#type' => 'hidden',
      '#value' => 'a77525a849b0888cf8d90460f',
    ];
    $form['id'] = [
      '#type' => 'hidden',
      '#value' => '807864fbe3',
    ];
    $form['ht'] = [
      '#type' => 'hidden',
      '#value' => 'de6a896233ca36b366356ab5f92a102df74ebcad:MTU5MDY4MTc2Mi45MzE=',
    ];
    $form['mc_signupsource'] = [
      '#type' => 'hidden',
      '#value' => 'hosted',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#title_display' => 'invisible',
      '#attributes' => ['placeholder' => 'Email Address'],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sign-Up'),
    ];
    $form['#attributes']['class'][] = 'centered-container';
    $form['#attributes']['class'][] = 'flex-container';
    return $form;
  }

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do since the form will submit off site.
  }

}
