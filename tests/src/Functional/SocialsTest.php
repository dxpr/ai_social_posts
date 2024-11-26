<?php

namespace Drupal\Tests\socials\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the basic functions of the Social Posts module.
 *
 * @group socials
 */
class SocialsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['socials', 'options', 'user'];

  /**
   * Basic CRUD operations on a SocialPost entity.
   */
  public function testEntity() {
    $assert = $this->assertSession();
    $web_user = $this->drupalCreateUser(['add social_post entity']);

    $this->drupalLogin($web_user);
    $assert->linkExists('Socials');
    $this->clickLink('Socials');
    $assert->linkExists('Add social_post');
    $this->clickLink($this->t('Add social_post'));

    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');

    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';
    $assert->fieldValueEquals('user_id[0][target_id]', $user_ref);

    $edit = [
      'name[0][value]' => 'test name',
      'first_name[0][value]' => 'test first name',
      'role' => 'administrator',
    ];
    $this->submitForm($edit, 'Save');

    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    $this->clickLink('test name');
  }

}
