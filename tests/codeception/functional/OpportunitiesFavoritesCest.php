<?php

use Faker\Factory;

/**
 * Class OpportunitiesFavoritesCest.
 *
 * @group favorites
 */
class OpportunitiesFavoritesCest {

  /**
   * Favorited opportunities will show in a list on the user's page.
   */
  public function testFavoriting(FunctionalTester $I) {
    $faker = Factory::create();
    $node = $I->createEntity([
      'type' => 'su_opportunity',
      'title' => $faker->text(),
    ]);

    $I->logInWithRole('stanford_student');

    $I->amOnPage($node->toUrl()->toString());
    $I->click('.flag a');
    $I->waitForAjaxToFinish();

    $I->amOnPage('/user');
    $I->canSee('1 Saved Item(s)');
    $I->click('Opportunities');
    $I->canSee($node->label());

    $I->amOnPage($node->toUrl()->toString());
    $I->click('.flag a');
    $I->wait(3);

    $query = \Drupal::database()
      ->select('watchdog', 'w')
      ->fields('w')
      ->orderBy('timestamp', 'DESC')
      ->range(0, 5)
      ->execute();
    while($row = $query->fetchAssoc()){
      echo json_encode($row) . PHP_EOL;
    }

    $I->waitForAjaxToFinish();

    $I->cantSee('Saved', '.flag');

    $I->amOnPage('/user/opportunities');
    $I->dontSee($node->label());
  }

}
