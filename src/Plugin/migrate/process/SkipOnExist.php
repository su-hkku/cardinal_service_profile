<?php

namespace Drupal\cardinal_service_profile\Plugin\migrate\process;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SkipOnExist.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_exist"
 * )
 */
class SkipOnExist extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory Service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * List of migration ids excluding those defined by the exclude config.
   *
   * @var string[]
   */
  protected $migrations = [];

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('database')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    static $here = FALSE;
    $source_ids = $row->getSourceIdValues();
    $migration_names = $this->getMigrationConfigs($source_ids);

    foreach ($migration_names as $name) {
      $query = $this->database->select("migrate_map_$name", 'm')
        ->fields('m')
        ->condition('destid1', 0, '>');

      $i = 1;
      foreach ($source_ids as $key_value) {
        $query->condition("sourceid$i", $key_value);
        $i++;
      }

      $count = $query->countQuery()->execute()->fetchField();
      if ((int) $count > 0) {
        throw new MigrateSkipRowException();
      }
    }
  }

  protected function getMigrationConfigs($source_ids) {
    if (!empty($this->migrations)) {
      return $this->migrations;
    }
    $prefix = 'migrate_plus.migration.';
    $config_names = $this->configFactory->listAll($prefix);
    $schema = $this->database->schema();

    foreach ($config_names as $name) {
      $name = str_replace($prefix, '', $name);

      if (
        (isset($this->configuration['migrate_exclude']) && in_array($name, $this->configuration['migrate_exclude'])) ||
        !$schema->tableExists("migrate_map_$name") ||
        !$schema->fieldExists("migrate_map_$name", 'sourceid' . count($source_ids))
      ) {
        continue;
      }

      $this->migrations[] = $name;
    }


    return $this->migrations;

  }

}
