<?php

/**
 * Tests on the mission statement paragraph type.
 */
class MissionStatementCest {

  /**
   * The paragraph should display on a basic page.
   */
  public function testMissionStatement(AcceptanceTester $I) {
    $paragraph = $I->createEntity([
      'type' => 'cardinal_mission_statement',
      'su_mission_text' => 'This is the mission statement whether you choose to accept it or not.',
      'su_mission_cta' => [
        'uri' => 'http://google.com',
        'title' => 'Verify your identity',
      ],
      'su_mission_lower_text' => [
        'value' => 'This paragraph will self destruct at the end of this test',
        'format' => 'stanford_minimal_html',
      ],
    ], 'paragraph');

    $node = $I->createEntity([
      'type' => 'stanford_page',
      'title' => 'Bond, Drupal Bond',
      'su_page_components' => [
        'target_id' => $paragraph->id(),
        'entity' => $paragraph,
        'settings' => json_encode([
          'row' => 0,
          'index' => 0,
          'width' => 12,
          'admin_title' => 'Card',
        ]),
      ],
    ]);

    $I->amOnPage($node->toUrl()->toString());
    $I->canSee('This is the mission statement whether you choose to accept it or not');
    $I->canSeeLink('Verify your identity');
    $I->canSeeLink('This paragraph will self destruct at the end of this test');
  }

}
