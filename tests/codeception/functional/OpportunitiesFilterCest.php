<?php

/**
 * Class OpportunitiesFilterCest.
 *
 * @group opportunities
 */
class OpportunitiesFilterCest {

  /**
   * Test the PDB is available and displays nodes when filtering.
   */
  public function testFilters(FunctionalTester $I) {
    $user = $I->createUserWithRoles(['site_manager', 'layout_builder_user']);
    $I->logInAs($user->id());
    $I->resizeWindow(1400, 1400);
    /** @var \Drupal\node\NodeInterface $node */
    $node = $I->createEntity([
      'type' => 'stanford_page',
      'title' => 'Filters Page',
    ]);
    $filter_url = $node->toUrl()->toString();
    $this->createOpportunityNodes($I);

    $I->amOnPage($filter_url);
    $I->click('Layout');
    $I->click('Add block');
    $I->waitForText('Choose a block');

    $I->click('Opportunities Filtering List');
    $I->waitForElementVisible('#layout-builder-modal');
    $I->click('Add block', '#layout-builder-modal');
    $I->waitForElementNotVisible('#layout-builder-modal');

    $I->click('Add block');
    $I->waitForText('Choose a block');
    $I->click('Opportunities: All Filtered');
    $I->waitForElementVisible('#layout-builder-modal');
    $I->click('Add block', '#layout-builder-modal');
    $I->waitForElementNotVisible('#layout-builder-modal');

    // Scroll up because the admin toolbar sometimes overlays the task links.
    $I->scrollTo(['css' => '.su-brand-bar']);
    $I->click('Save layout');
    $I->canSeeNumberOfElements('.views-row', [1, 99]);

    $I->waitForElementVisible('.MuiFormControl-root', 5);
    $I->click('.main-filters .filter-select-container:first-child > div');
    $I->click('.MuiAutocomplete-listbox li[aria-disabled="false"]');

    $I->click('.main-filters .filter-select-container:nth-child(2) > div');
    $I->click('.MuiAutocomplete-listbox li[aria-disabled="false"]');

    $I->click('.main-filters .filter-select-container:nth-child(3) > div');
    $I->click('.MuiAutocomplete-listbox li[aria-disabled="false"]');

    $I->click('Search', '#opportunities-filter-list');

    $I->canSeeNumberOfElements('.views-row', [1, 999]);
    $I->canSeeNumberOfElements('.su-opportunity-result', [1, 999]);
    $I->canSee('Showing Results For:');
  }

  /**
   * Test the exposed filters action works correctly.
   */
  public function testViewExposedFilter(FunctionalTester $I) {
    $user = $I->createUserWithRoles(['site_manager', 'layout_builder_user']);
    $I->logInAs($user->id());
    $node = $I->createEntity([
      'type' => 'stanford_page',
      'title' => 'Filters Page',
    ]);
    $filter_url = $node->toUrl()->toString();
    $this->createOpportunityNodes($I);

    $node = $I->createEntity([
      'type' => 'stanford_page',
      'title' => 'Test Page',
    ]);
    $I->amOnPage($node->toUrl()->toString());

    $I->click('Layout');
    $I->click('Add block');
    $I->waitForAjaxToFinish();
    $I->click('Exposed form: su_opportunities-filtered_all');
    $I->waitForAjaxToFinish();
    $I->fillField('Form action URL', $filter_url);

    $tries = 0;
    while ($I->grabMultiple('.layout-builder-add-block') && $tries < 5) {
      $I->click('Add block', '.layout-builder-add-block');
      $I->wait(1);
      $tries++;
    }

    $I->scrollTo('.su-masthead');
    $I->click('Save layout');
    $I->click('Apply');
    $I->canSeeInCurrentUrl($filter_url);
  }

  /**
   * Create some opportunity nodes.
   */
  protected function createOpportunityNodes(FunctionalTester $I) {
    $terms = $this->createTerms($I);
    for ($j = 0; $j <= 10; $j++) {
      $values = [
        'type' => 'su_opportunity',
        'title' => "Opportunity $j",
        'su_opp_open_to' => $terms['su_opportunity_open_to'][$j % 3]->id(),
        'su_opp_location' => $terms['su_opportunity_location'][($j + 1) % 3]->id(),
        'su_opp_time_year' => $terms['su_opportunity_time'][($j + 2) % 3]->id(),
        'su_opp_type' => $terms['su_opportunity_type'][$j % 3]->id(),
        'su_opp_service_theme' => $terms['su_opportunity_service_theme'][$j % 3]->id(),
      ];
      $I->createEntity($values);
    }
  }

  /**
   * Create some taxonomy terms.
   */
  protected function createTerms(FunctionalTester $I) {
    $vids = [
      'su_opportunity_open_to',
      'su_opportunity_location',
      'su_opportunity_time',
      'su_opportunity_type',
      'su_opportunity_service_theme',
    ];
    $terms = [];
    foreach ($vids as $vid) {
      $terms[$vid][] = $I->createEntity([
        'vid' => $vid,
        'name' => 'foo',
      ], 'taxonomy_term');
      $terms[$vid][] = $I->createEntity([
        'vid' => $vid,
        'name' => 'bar',
      ], 'taxonomy_term');
      $terms[$vid][] = $I->createEntity([
        'vid' => $vid,
        'name' => 'baz',
      ], 'taxonomy_term');
    }
    return $terms;
  }

}
