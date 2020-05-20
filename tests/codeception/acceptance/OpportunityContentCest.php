<?php

/**
 * Spotlight content type tests.
 * @group opportunities
 */
class OpportunityContentCest {

  /**
   * Create a new piece of Opportunity content.
   */
  public function testOpportunityContentCreation(\AcceptanceTester $I) {
    $I->logInWithRole('site_manager');
    $I->amOnPage('/node/add/su_opportunity');
    $I->fillField('Title', 'Test Opportunity');
    $I->fillField('Body', 'Lorem Ipsum');
    $I->click('Save');
    $I->canSee('Test Opportunity');
    $I->canSee('Lorem Ipsum');
  }

  /**
   * The importer should bring in some content.
   */
  public function testImporter(\AcceptanceTester $I) {
    $I->logInWithRole('administrator');
    $I->amOnPage('/admin/structure/migrate/manage/opportunities/migrations');
    $I->canSee('solo_opportunities');
    $total_items = $I->grabTextFrom('table tbody td:nth-child(4)');
    $I->assertGreaterOrEquals(1, (int) $total_items);

    // These steps require increased execution time. Figure that out and then
    // these can be uncommented.
    // $I->runDrush('mim solo_opportunities');
    // $I->amOnPage('/admin/content');
    // $I->canSee('Opportunity', '.vbo-table');
  }

  /**
   * The related opportunities block should appear on the opportunity page.
   */
  public function testRelatedOpportunities(AcceptanceTester $I) {
    $terms = $this->createTaxonomyTerms($I, [
      'Foo Bar Baz',
    ], 'su_opportunity_type');

    $I->createEntity([
      'type' => 'su_opportunity',
      'title' => 'Foo Bar',
      'su_opp_type' => reset($terms)->id(),
    ]);
    $node = $I->createEntity([
      'type' => 'su_opportunity',
      'title' => 'Bar Foo',
      'su_opp_type' => reset($terms)->id(),
    ]);

    $I->amOnPage($node->toUrl()->toString());
    $I->canSee('Bar Foo', 'h1');
    $I->canSee('Related Opportunities', 'h2');
    $I->canSee('Foo Bar', 'h2');
  }

  /**
   * Create taxonomy terms for testing.
   */
  protected function createTaxonomyTerms(\AcceptanceTester $I, array $terms, $vocab) {
    $created_terms = [];
    foreach ($terms as $term) {
      $created_terms[] = $I->createEntity([
        'name' => $term,
        'vid' => $vocab,
      ], 'taxonomy_term');
    }
    return $created_terms;
  }

}
