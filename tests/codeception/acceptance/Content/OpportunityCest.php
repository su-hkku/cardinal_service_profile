<?php

use Faker\Factory;

/**
 * Test opportunity content type.
 *
 * @group opportunity
 */
class OpportunityCest {

  /**
   * Faker.
   *
   * @var \Faker\Generator
   */
  protected $faker;

  /**
   * Test Constructor.
   */
  public function __construct() {
    $this->faker = Factory::create();
  }

  public function testContentAccess(AcceptanceTester $I) {
    $I->logInWithRole('site_manager');
    $I->amOnPage('/node/add');
    $I->cantSeeLink('Opportunity', '/node/add/stanford_opportunity');
    $I->amOnPage('/admin/structure/taxonomy');
    $I->cantSeeLink('List terms', '/admin/structure/taxonomy/manage/opportunity_type/overview');
    $I->amOnPage('/user/logout');
    $I->click('Log out', 'form');

    $I->logInWithRole('contibutor');
    $I->amOnPage('/node/add');
    $I->cantSeeLink('Opportunity', '/node/add/stanford_opportunity');
    $I->amOnPage('/admin/structure/taxonomy');
    $I->cantSeeLink('List terms', '/admin/structure/taxonomy/manage/opportunity_type/overview');
    $I->amOnPage('/user/logout');
    $I->click('Log out', 'form');

    $user = $I->createUserWithRoles(['site_manager', 'opportunity_editor']);
    $I->logInAs($user->getAccountName());
    $I->amOnPage('/node/add');
    $I->canSeeLink('Opportunity', '/node/add/stanford_opportunity');
    $I->amOnPage('/admin/structure/taxonomy');
    $I->canSeeLink('List terms', '/admin/structure/taxonomy/manage/opportunity_type/overview');
    $I->amOnPage('/admin/structure/taxonomy/manage/opportunity_tag_filters/add');
    $I->canSeeInField('Name', '');
    $I->amOnPage('/admin/structure/taxonomy/manage/opportunity_sponsor/add');
    $I->canSeeInField('Name', '');
    $I->amOnPage('/admin/structure/taxonomy/manage/opportunity_type/add');
    $I->canSeeInField('Name', '');
  }

}
