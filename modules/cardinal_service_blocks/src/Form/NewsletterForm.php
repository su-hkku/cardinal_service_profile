<?php

namespace Drupal\cardinal_service_blocks\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterForm.
 *
 * @package Drupal\cardinal_service_blocks\Form
 */
class NewsletterForm extends FormBase {

  /**
   * Guzzle client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $guzzle;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('http_client'));
  }

  /**
   * NewsletterForm constructor.
   *
   * @param \GuzzleHttp\ClientInterface $guzzle
   */
  public function __construct(ClientInterface $guzzle) {
    $this->guzzle = $guzzle;
  }

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
    $form['#action'] = $form_state->getBuildInfo()['action_url'];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#title_display' => 'invisible',
      '#attributes' => ['placeholder' => 'Email Address'],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sign-Up'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
      ],
    ];
    $form['#attributes']['class'][] = 'centered-container';
    $form['#attributes']['class'][] = 'flex-container';
    return $form;
  }

  /**
   * Ajax submit handler for the newsletter signup.
   *
   * @param array $form
   *   Complete Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response commands.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $selector = '.cardinal-service-blocks-newsletter';
    $response = new AjaxResponse();
    if (!$form_state->getValue('email')) {
      $message = $this->getFormMessage('error', $this->t('Email address is required'));
      $response->addCommand(new PrependCommand($selector, $message));
      return $response;
    }

    try {
      $this->guzzle->request('POST', $form_state->getBuildInfo()['action_url'], ['form_params' => ['MERGE0' => $form_state->getValue('email')]]);
      $message = $this->getFormMessage('success', $this->t('Thanks for signing up'));
      return $response->addCommand(new ReplaceCommand($selector, $message));
    } catch (\Exception $e) {
      $message = $this->getFormMessage('error', $this->t('Unable to sign up for email newsletter at this time. Please try again later.'));
    }

    $response->addCommand(new PrependCommand($selector, $message));
    return $response;
  }

  /**
   * Get the rendered message markup to return to the user.
   *
   * @param string $status
   *   Message status: success, error, or notice.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   Text message to tell the user.
   *
   * @return string
   *   Rendered html message.
   */
  protected function getFormMessage($status, $message) {
    $form_message = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'su-alert',
          "su-alert--$status",
        ],
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $message,
        '#attributes' => ['class' => ['su-alert__body']],
      ],
    ];
    return render($form_message);
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
