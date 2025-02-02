<?php

namespace Drupal\Tests\cardinal_service_profile\Kernel\Plugin\InstallTask;

use Drupal\config_pages\Entity\ConfigPagesType;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Driver\Exception\Exception;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Drupal\cardinal_service_profile\Plugin\InstallTask\SiteSettings;

/**
 * Class SiteSettingsTest.
 *
 * @coversDefaultClass \Drupal\cardinal_service_profile\Plugin\InstallTask\SiteSettings
 */
class SiteSettingsTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'system',
    'config_pages',
    'config_pages_overrides',
    'externalauth',
    'path_alias',
    'user',
    'field',
    'node',
  ];

  /**
   * The response guzzle mock object will return.
   *
   * @var \GuzzleHttp\Psr7\Stream
   */
  protected $guzzleResponse;

  /**
   * {@inheritDoc}
   */
  public function setup(): void {
    parent::setUp();
    $this->setInstallProfile('cardinal_service_profile');

    $this->installEntitySchema('user');
    $this->installEntitySchema('user_role');
    $this->installEntitySchema('field_storage_config');
    $this->installEntitySchema('field_config');
    $this->installEntitySchema('config_pages_type');
    $this->installEntitySchema('config_pages');
    $this->installEntitySchema('node');
    $this->installSchema('externalauth', 'authmap');
    $this->installSchema('system', ['sequences']);
    $this->installConfig('system');

    Role::create(['label' => 'Owner', 'id' => "site_manager"])->save();

    $config_page_type = ConfigPagesType::create([
      'id' => 'stanford_basic_site_settings',
      'menu' => [],
      'context' => [],
    ]);
    $config_page_type->setThirdPartySetting('config_pages_overrides', $this->randomMachineName(), [
      'field' => 'su_site_name',
      'delta' => '0',
      'column' => 'value',
      'config_name' => 'system.site',
      'config_item' => 'name',
    ]);
    $config_page_type->setThirdPartySetting('config_pages_overrides', $this->randomMachineName(), [
      'field' => 'su_site_email',
      'delta' => '0',
      'column' => 'value',
      'config_name' => 'system.site',
      'config_item' => 'mail',
    ]);

    $config_page_type->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'su_site_email',
      'entity_type' => 'config_pages',
      'type' => 'email',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'entity_type' => 'config_pages',
      'field_storage' => $field_storage,
      'bundle' => 'stanford_basic_site_settings',
      'label' => 'Email',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'su_site_name',
      'entity_type' => 'config_pages',
      'type' => 'string',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'entity_type' => 'config_pages',
      'field_storage' => $field_storage,
      'bundle' => 'stanford_basic_site_settings',
      'label' => 'Name',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'su_hide_ext_link_icons',
      'entity_type' => 'config_pages',
      'type' => 'boolean',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'entity_type' => 'config_pages',
      'field_storage' => $field_storage,
      'bundle' => 'stanford_basic_site_settings',
      'label' => 'Hide Ext Links',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'su_site_created',
      'entity_type' => 'config_pages',
      'type' => 'timestamp',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'entity_type' => 'config_pages',
      'field_storage' => $field_storage,
      'bundle' => 'stanford_basic_site_settings',
      'label' => 'Created',
    ])->save();

    drupal_flush_all_caches();

    $data = json_encode([
      'result' => [
        [
          [
            'webSiteTitle' => 'foo bar',
            'webSiteAddress' => 'default',
            'sunetId' => 'barfoo',
            'fullName' => 'Bar Foo',
            'email' => 'barfoo@stanford.edu',
            'webSiteOwners' => [
              [
                'sunetId' => 'barfoo',
                'fullName' => 'Bar Foo',
                'email' => 'barfoo@stanford.edu',
              ],
              [
                'sunetId' => 'bazbar',
                'fullName' => 'Baz Bar',
                'email' => 'bazbar@stanford.edu',
              ],
            ],
          ],
        ],
      ],
    ]);

    $resource = fopen('php://memory', 'r+');
    fwrite($resource, $data);
    rewind($resource);
    $this->guzzleResponse = new Stream($resource);
  }

  /**
   * Add the service with appropriate mock properties.
   *
   * @param string|null $throw_guzzle_exception
   *   Class name for guzzle to throw.
   *
   * @throws \Exception
   */
  protected function runInstallTask($throw_guzzle_exception = NULL) {
    $this->container->set('http_client', $this->getMockGuzzle($throw_guzzle_exception));
    $plugin = TestSiteSettings::create($this->container, [], '', []);
    $install_state['forms']['install_configure_form']['site_name'] = 'foo bar';
    $plugin->runTask($install_state);
  }

  /**
   * Get a mocked logger factory service.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock logger service.
   */
  protected function getMockLogger() {
    $logger_channel = $this->createMock(LoggerChannelInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')->willReturn($logger_channel);
    return $logger_factory;
  }

  /**
   * Get the mock guzzle client service.
   *
   * @param string|null $throw_guzzle_exception
   *   The class of the exception to throw.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The mocked service.
   */
  protected function getMockGuzzle($throw_guzzle_exception = NULL) {
    $client = $this->createMock(ClientInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $request = $this->createMock(RequestInterface::class);

    switch ($throw_guzzle_exception) {
      case GuzzleException::class:
        $response->method('getBody')
          ->willThrowException(new ClientException('Failed here', $request, $response));
        break;

      case Exception::class:
        $response->method('getBody')
          ->willThrowException(new \Exception('Failed here'));
        break;

      default:
        $response->method('getBody')
          ->willReturnReference($this->guzzleResponse);
        break;
    }

    $client->method('request')->willReturn($response);
    return $client;
  }

  /**
   * When the service gets a correct API response, the config will change.
   */
  public function testValidInstallTasks() {
    $this->assertNotEquals('foo bar', \Drupal::config('system.site')
      ->get('name'));

    $this->runInstallTask();

    drupal_flush_all_caches();
    $this->assertEquals('foo bar', \Drupal::config('system.site')->get('name'));
    $this->assertEquals('barfoo@stanford.edu', \Drupal::config('system.site')
      ->get('mail'));

    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['name' => ['barfoo', 'bazbar']]);
    $this->assertCount(2, $users);
    $this->assertEquals('https://foo bar.sites.stanford.edu', \Drupal::state()
      ->get('xmlsitemap_base_url'));
  }

  /**
   * When the API can't find the site, no changes will be made.
   */
  public function testSiteNotFound() {
    $data = json_encode(['result' => [['message' => 'no records found']]]);
    $resource = fopen('php://memory', 'r+');
    fwrite($resource, $data);
    rewind($resource);
    $this->guzzleResponse = new Stream($resource);

    $this->runInstallTask();

    drupal_flush_all_caches();
    $this->assertEmpty(\Drupal::config('system.site')->get('name'));
    $this->assertEmpty(\Drupal::config('system.site')->get('mail'));
  }

  /**
   * When the API doesn't return a json object, no changes will be made.
   */
  public function testIncorrectApiResponse() {
    $resource = fopen('php://memory', 'r+');
    fwrite($resource, $this->randomString());
    rewind($resource);
    $this->guzzleResponse = new Stream($resource);

    $this->runInstallTask();

    drupal_flush_all_caches();
    $this->assertEmpty(\Drupal::config('system.site')->get('name'));
    $this->assertEmpty(\Drupal::config('system.site')->get('mail'));
  }

  /**
   * If exceptions are thrown, the service should be able to handle it.
   */
  public function testExceptions() {
    $this->runInstallTask(Exception::class);

    drupal_flush_all_caches();
    $this->assertEmpty(\Drupal::config('system.site')->get('name'));
    $this->assertEmpty(\Drupal::config('system.site')->get('mail'));

    $this->runInstallTask(GuzzleException::class);

    drupal_flush_all_caches();
    $this->assertEmpty(\Drupal::config('system.site')->get('name'));
    $this->assertEmpty(\Drupal::config('system.site')->get('mail'));
  }

}

/**
 * Test class.
 */
class TestSiteSettings extends SiteSettings {

  /**
   * {@inheritDoc}
   */
  protected static function isAhEnv() {
    return TRUE;
  }

}
