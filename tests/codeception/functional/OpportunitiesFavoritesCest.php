<?php

use Faker\Factory;

/**
 * Class OpportunitiesFavoritesCest.
 *
 * @group favorites
 */
class OpportunitiesFavoritesCest {

  public function testFavoriting(AcceptanceTester $I) {
    $faker = Factory::create();
    $node = $I->createEntity(['type' => 'su_opportunity', 'title' => $faker->text(200)]);
    $I->logInWithRole('stanford_student');
    $I->amOnPage($node->toUrl()->toString());
    $I->click('.flag a');
    $I->waitForAjaxToFinish();
    $I->amOnPage('/user');
    $I->canSee($node->label());
    $I->amOnPage($node->toUrl()->toString());
    $I->click('.flag a');
    $I->amOnPage('/user');
    $I->dontSee($node->label());
  }

}
