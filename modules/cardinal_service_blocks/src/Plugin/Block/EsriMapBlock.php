<?php

namespace Drupal\cardinal_service_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a 'NewsletterBlock' block.
 *
 * @Block(
 *  id = "esri_map",
 *  admin_label = @Translation("CS Esri Map"),
 * )
 */
class EsriMapBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'height' => 500,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height of the frame'),
      '#default_value' => $this->configuration['height'] ?? 500,
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['height'] = $form_state->getValue('height');
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#value' => '',
      '#attributes' => [
        'src' => 'https://cardinalquarterfellows.cardinalservice.org/',
        'title' => $this->t('ESRI Map'),
        'height' => $this->configuration['height'] ?? 500,
      ],
    ];
  }

}
