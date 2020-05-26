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
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email Address'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sign-Up'),
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do since the form will submit off site.
  }

}
