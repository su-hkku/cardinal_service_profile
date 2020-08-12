<?php

namespace Drupal\cardinal_service_profile_helper\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CsvImporterForm.
 */
class CsvImporterForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_importer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = ['#markup' => $this->getTemplateLinks()];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#required' => TRUE,
      '#options' => [
        'su_spotlight' => 'Spotlight',
        'su_opportunity' => 'Opportunity',
      ],
      '#empty_option' => $this->t('- Select -'),
    ];
    $form['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('CSV File'),
      '#upload_location' => 'temporary://',
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager->getStorage('file')
      ->load($form_state->getValue(['csv', 0]));
    dpm($file->getFileUri());

    $file->delete();
  }

  protected function getTemplateLinks() {
    $replacements = [
      '@people' => Link::createFromRoute(t('People'), 'cardinal_service.csv_template', ['node_type' => 'stanford_person'])
        ->toString(),
      '@opportunities' => Link::createFromRoute(t('Opportunities'), 'cardinal_service.csv_template', ['node_type' => 'su_opportunity'])
        ->toString(),
      '@stories' => Link::createFromRoute(t('Spotlight'), 'cardinal_service.csv_template', ['node_type' => 'su_spotlight'])
        ->toString(),
      '@news' => Link::createFromRoute(t('News'), 'cardinal_service.csv_template', ['node_type' => 'stanford_news'])
        ->toString(),
      '@events' => Link::createFromRoute(t('Events'), 'cardinal_service.csv_template', ['node_type' => 'stanford_event'])
        ->toString(),
    ];
    return $this->t('Download an empty CSV template for @people, @opportunities, @stories, @events, or @news.', $replacements);
  }

}
