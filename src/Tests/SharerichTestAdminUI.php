<?php

namespace Drupal\sharerich\Tests;

use Drupal\simpletest\WebTestBase;
/**
 * Tests Sharerich on User forms.
 *
 * @group sharerich
 */
class SharerichTestAdminUI extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('contact', 'user', 'sharerich');

  /**
   * A user with the 'Administer sharerich' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser(array(
      'access administration pages',
    ));
  }

  function testLinkToConfig() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/modules');
    $link = $this->xpath('//a[contains(@href, :href) and contains(@id, :id)]', [
      ':href' => 'admin/structure/sharerich',
      ':id' => 'edit-modules-sharing-sharerich-links-configure'
    ]);
    $this->assertTrue(count($link) === 1, 'Link to config is present');
  }

}
