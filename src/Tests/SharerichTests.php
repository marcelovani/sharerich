<?php

namespace Drupal\sharerich\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Component\Serialization\Json;

/**
 * Sharerich tests.
 *
 * @group sharerich
 */
class SharerichTests extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'token', 'contextual', 'node', 'field', 'text', 'sharerich');

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
    $this->adminUser = $this->drupalCreateUser(array(
      'access administration pages',
      'administer sharerich',
      'administer blocks',
      'access contextual links',
    ), 'Sharerich Admin', TRUE); //@todo remove TRUE
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
      ':id' => 'edit-modules-sharing-sharerich-links-configure'
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
      $this->assertTrue(count($element) === 1, t('The :item is checked.', array(':item' => ucfirst($item))));

      $actual = (string) $this->xpath('//textarea[@name="services[' . $item . '][markup]"]')[0];
      $expected = (string) $this->xpath('//input[@type="hidden"][@name="services[' . $item . '][default_markup]"]/@value')[0];
      // Normalize strings.
      $actual=preg_replace('/(\r\n|\r|\n|\s|\t)/s'," ",$actual);
      $expected=preg_replace('/(\r\n|\r|\n|\s|\t)/s'," ",$expected);
      $this->assertTrue($actual == $expected, t('The :item widget is correct.', array(':item' => ucfirst($item))));
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

    $text = $this->xpath('//div[@id="block-sharerich-block"]/h2/text()')[0][0];
    $this->assertEqual($text, t('Share this'), 'The title of sharerich block is correct');

    $element = $this->xpath('//div[contains(@class, "sharerich-wrapper") and contains(@class, "sharerich-vertical") and contains(@class, "sharerich-sticky")]');
    $this->assertTrue(!empty($element), 'Found a sticky sharerich block');

    foreach ($this->services as $item) {
      $text = $this->xpath('//div[@id="block-sharerich-block"]//ul/li[@class="rrssb-' . $item . '"]//span[@class="rrssb-text"]/text()')[0][0];
      $this->assertEqual($text, $item, t('The text of :item button is correct', array(':item' => $item)));
    }

    // Test that tokens were rendered correctly.
    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', array(
      ':li_class' => 'rssb-email',
      ':href' => 'mailto:?subject=Sharerich%20page&body=http',
    ), "Email Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', array(
      ':li_class' => 'rssb-facebook',
      ':href' => 'https://www.facebook.com/sharer/sharer.php?u=http',
    ), "Facebook Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', array(
      ':li_class' => 'rssb-tumblr',
      ':href' => 'http://www.tumblr.com/share?s=&v=3&t=Sharerich%20page&u=http',
    ), "Tumblr Tokens rendered correctly.");

    $this->assertElementByXPath('//div[@id="block-sharerich-block"]//ul/li[contains(@class, :li_class)]/a[contains(@href, :href)]', array(
      ':li_class' => 'rssb-twitter',
      ':href' => 'https://twitter.com/intent/tweet?url=http',
    ), "Twitter Tokens rendered correctly.");

    // Test contextual links.
    $block_id = 'sharerich_block';
    $id = 'block:block=' . $block_id . ':langcode=en|sharerich:sharerich=default:langcode=en';
    // @see \Drupal\contextual\Tests\ContextualDynamicContextTest:assertContextualLinkPlaceHolder()
    $this->assertRaw('<div data-contextual-id="' . $id . '"></div>', t('Contextual link placeholder with id @id exists.', array('@id' => $id)));

    // Get server-rendered contextual links.
    // @see \Drupal\contextual\Tests\ContextualDynamicContextTest:renderContextualLinks()
    $post = array('ids[0]' => $id);
    $response = $this->drupalPost('contextual/render', 'application/json', $post, array('query' => array('destination' => 'test-page')));
    $this->assertResponse(200);
    $json = Json::decode($response);
    $this->assertIdentical($json[$id], '<ul class="contextual-links"><li class="block-configure"><a href="/admin/structure/block/manage/sharerich_block">Configure block</a></li><li class="entitysharerich-edit-form"><a href="/admin/structure/sharerich/default">Edit Sharerich set</a></li></ul>', t('Contextual links are correct.'));
  }
}
