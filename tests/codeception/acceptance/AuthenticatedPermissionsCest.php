<?php

/**
 * Test the restrictions on authenticated users.
 */
use StanfordCaravan\Codeception\Drupal\DrupalUser;

class AuthenticatedPermissionsCest {


  /**
   * Make sure authenticated users can't access things they should not.
   */
  public function testAuthenticatedUserRestrictions(AcceptanceTester $I) {
    $I->logInWithRole('authenticated');
    $I->amOnPage('/');
    $I->canSeeResponseCodeIs(200);
    $I->amOnPage('/admin');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/content');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/structure');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/appearance');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/modules');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/config');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/people');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/reports');
    $I->canSeeResponseCodeIs(403);
    $I->amOnPage('/admin/reports/status');
    $I->canSeeResponseCodeIs(403);
  }

  /**
   * Make sure authenticated users can access things they should.
   */
  public function testAuthenticatedUserPermissions(AcceptanceTester $I) {
    $I->logInWithRole('authenticated');
    $I->amOnPage('/patterns');
    $I->canSeeResponseCodeIs(200);
  }

  /**
   * Site Manager cannot escalate own role above Site Manager
   * Site Manager cannot escalate the role of others above Site Manager
   * PHP entered in a redirect gets sanitized
   * PHP code gets sanitized on content creation
   * Try to upload a PHP file in Media and it fails form validation
   * Try to upload a PHP file as a favicon in theme settings and have it fail (error message for me was "For security reasons, your upload has been renamed to foo.php.txt.")
   * Try to upload a PHP file as a logo in theme settings and have it fail (error message for me was "The image file is invalid or the image type is not allowed. Allowed types: gif, jpe, jpeg, jpg, png")
   */

   public function testSiteManagerEscalationSelf(AcceptanceTester $I) {
     $site_manager = $I->logInWithRole('site_manager');
     $site_manager_id = $site_manager->getUid();
     $I->amOnPage('/admin/people');
     $I->canSee($site_manager->name);
     $I->click(['link' => $site_manager->name]);
     $I->click('.roles.tabs__tab a');
     $I->canSeeInCurrentUrl("/user/$site_manager_id/roles");
     $I->canSee('Administrator');
     $I->checkOption('#edit-role-change-administrator');
     $I->click('#edit-submit');
     $I->canSeeInCurrentUrl("/user/$site_manager_id/roles");
     $I->canSee("The roles have been updated.");
   }

   public function testSiteManagerEscalationOthers(AcceptanceTester $I) {
     return;
   }

   public function testPhpInRedirect(AcceptanceTester $I) {
     return;
   }

   public function testPhpInContent(AcceptanceTester $I) {
     return;
   }

   public function testPhpUploadInMedia(AcceptanceTester $I) {
     return;
   }

   public function testPhpUploadInFavicon(AcceptanceTester $I) {
     return;
   }

   public function testPhpUploadInLogo(AcceptanceTester $I) {
     return;
   }

}
