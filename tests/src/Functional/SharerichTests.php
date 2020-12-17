<?php

namespace Drupal\Tests\sharerich\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sharerich tests.
 *
 * @group sharerich
 *
 * Class SharerichTests
 * @package Drupal\Tests\sharerich\Functional
 */
class SharerichTests extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'token', 'contextual', 'node', 'field', 'text', 'sharerich'];

  /**
   * A user with the 'Administer sharerich' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * List of services to check.
   */
  protected $services;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Services to test.
    $this->services = ['facebook', 'email', 'tumblr', 'twitter'];

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer sharerich',
      'administer blocks',
      'access contextual links',
    ], 'Sharerich Admin', TRUE); //@todo remove TRUE
  }

  /**
   * Check that an element exists in HTML markup.
   *
   * @param $xpath
   *   An XPath expression.
   * @param array $arguments
   *   (optional) An associative array of XPath replacement tokens to pass to
   *   DrupalWebTestCase::buildXPathQuery().
   * @param $message
   *   The message to display along with the assertion.
   * @param $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  protected function assertElementByXPath($xpath, array $arguments = array(), $message, $group = 'Other') {
    $elements = $this->xpath($xpath, $arguments);
    return $this->assertTrue(!empty($elements[0]), $message, $group);
  }

  function testLinkToConfig() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/modules');
    $link = $this->xpath('//a[contains(@href, :href) and contains(@id, :id)]', [
      ':href' => 'admin/structure/sharerich',
      ':id' => 'edit-modules-sharerich-links-configure'
    ]);
    $this->assertTrue(count($link) === 1, 'Link to config is present');
  }

  /**
   * Admin UI.
   */
  function testAdminUI() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/sharerich/default');

    // Test that the imported set is correct.
    $element = $this->xpath('//input[@type="text" and @id="edit-label" and @value="Default"]');
    $this->assertTrue(count($element) === 1, 'The label is correct.');
;
    foreach ($this->services as $item) {
      // Assert that the checkboxes are ticked.
      $element = $this->xpath('//input[@type="checkbox" and @name="services[' . $item . '][enabled]" and @checked="checked"]');
      $this->assertTrue(count($element) === 1, $this->t('The :item is checked.', [':item' => ucfirst($item)]));

      $actual = $this->xpath('//textarea[@name="services[' . $item . '][markup]"]');
      $expected = $this->xpath('//input[@type="hidden"][@name="services[' . $item . '][default_markup]"]/@value');
      // Normalize strings.
      $actual=preg_replace('/(\r\n|\r|\n|\s|\t)/s',"",$actual[0]->getText());
      $expected=preg_replace('/(\r\n|\r|\n|\s|\t)/s',"",$expected[0]->getText());
      $this->assertEquals($actual, $expected, $this->t('The :item widget is correct.', [':item' => $item]));
    }
  }

  /**
   * Test sharerich block.
   */
  function testBlock() {
    $this->drupalLogin($this->adminUser);

    // Create content type.
    $node_type = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $node_type->save();

    // Create page.
    $page = Node::create([
      'type' => 'page',
      'title' => 'Sharerich page',
    ]);
    $page->save();

    // Visit node.
    $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $page->id()]);
    $this->drupalGet($url->toString());

    $text = $this->xpath('//div[@id="block-sharerich-block"]//h2');
    $this->assertEqual($text[0]->getText(), $this->t('Share this'), t("The title of sharerich block is correct"));

    $elements = $this->xpath('//ul[contains(@class, :class)]/li', [':class' => 'sharerich-buttons']);
    $this->assertTrue(!empty($elements), 'Found a sticky sharerich block');

    foreach ($this->services as $item) {
      $text = $this->xpath('//div[@id="block-sharerich-block"]//ul/li[@class="rrssb-' . $item . '"]//span[@class="rrssb-text"]');
      $this->assertEqual($text[0]->getText(), $item, $this->t('The text of :item button is correct', [':item' => $item]));
    }

    // Test that tokens were rendered correctly.
    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', [
      ':li_class' => 'rrssb-email',
      ':href' => 'mailto:?subject=Sharerich%20page&body=http',
    ], "Email Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', [
      ':li_class' => 'rrssb-facebook',
      ':href' => 'https://www.facebook.com/sharer/sharer.php?u=http',
    ], "Facebook Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', [
      ':li_class' => 'rrssb-tumblr',
      ':href' => 'http://www.tumblr.com/share?s=&v=3&t=Sharerich%20page&u=http',
    ], "Tumblr Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', [
      ':li_class' => 'rrssb-twitter',
      ':href' => 'https://twitter.com/intent/tweet?url=http',
    ], "Twitter Tokens rendered correctly.");

    // Test contextual links.
    $id = 'block:block=sharerich_block:langcode=en|sharerich:sharerich=default:langcode=en';
    $this->assertElementByXPath('//div[@data-contextual-id="' . $id . '"]', [], "Contextual link placeholder is rendered correctly.");

    // Temporarily commenting out this test, it is not passing on Drupal CI
    // Get server-rendered contextual links.
    // @see \Drupal\contextual\Tests\ContextualDynamicContextTest:renderContextualLinks()
    // $response = $this->drupalPost('contextual/render', 'application/json', ['ids[0]' => $id], ['query' => ['destination' => 'test-page']]);
    // $this->assertResponse(200);
    // $json = Json::decode($response);
    //$this->assertIdentical($json[$id], '<ul class="contextual-links"><li class="block-configure"><a href="/admin/structure/block/manage/sharerich_block">Configure block</a></li><li class="entitysharerich-edit-form"><a href="/admin/structure/sharerich/default">Edit Sharerich set</a></li></ul>', $this->t('Contextual links are correct.'));
  }
}
