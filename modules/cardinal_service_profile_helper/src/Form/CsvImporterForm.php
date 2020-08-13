<?php

namespace Drupal\cardinal_service_profile_helper\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CsvImporterForm.
 */
class CsvImporterForm extends FormBase {

  /**
   * Entity Type Manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File System service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_system'),
      $container->get('cache.default')
    );
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->cache = $cache;
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
    $form['migration'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#required' => TRUE,
      '#options' => [
        'csv_spotlight' => 'Spotlight',
        'csv_opportunities' => 'Opportunity',
      ],
      '#empty_option' => $this->t('- Select -'),
    ];
    $form['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('CSV File'),
      '#upload_location' => 'temporary://',
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Invalidate the migrations so that we can alter the plugin after setting
    // the state for the file path.
    Cache::invalidateTags(['migration_plugins']);
    \Drupal::database()->truncate('migrate_map_' . $form_state->getValue('migration'))->execute();
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager->getStorage('file')
      ->load($form_state->getValue(['csv', 0]));
    $file_path = $this->fileSystem->realpath($file->getFileUri());
    $migration = $form_state->getValue('migration');

    // Set the cache for the csv file path for only 4 minutes since it will be
    // fast for the importer.
    $this->cache->set('migration:csv_path', [
      'migration' => $migration,
      'path' => $file_path,
    ], time() + 240);

    try {
      $migrations = stanford_migrate_migration_list();
      /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
      $migration = $migrations['opportunities'][$migration];
      stanford_migrate_execute_migration($migration, $migration->id());
      $file->delete();
    }
    catch (\Exception $e) {
      $this->logger('cardinal_service')
        ->error($this->t('CSV Importer failed: @message', ['@message' => $e->getMessage()]));
      $this->messenger()
        ->addError($this->t('Unable to import CSV. Review the logs for more information'));
    }
  }

  protected function getTemplateLinks() {
    $replacements = [
      '@opportunities' => Link::createFromRoute(t('Opportunities'), 'cardinal_service.csv_template', ['migration' => 'csv_opportunities'])
        ->toString(),
      '@stories' => Link::createFromRoute(t('Spotlight'), 'cardinal_service.csv_template', ['migration' => 'csv_spotlight'])
        ->toString(),
    ];
    return $this->t('Download an empty CSV template for @opportunities or @stories.', $replacements);
  }

}
