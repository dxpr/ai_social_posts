<?php

namespace Drupal\Tests\socials\Functional;

use Drupal\socials\Entity\SocialPost;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\examples\Functional\ExamplesBrowserTestBase;
use Drupal\Core\Url;

/**
 * Tests the basic functions of the Socials module.
 *
 * @ingroup socials
 *
 * @group socials
 * @group examples
 */
class ContentEntityExampleTest extends ExamplesBrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['socials', 'block', 'field_ui'];

  /**
   * Basic tests for Socials.
   */
  public function testContentEntityExample() {
    $assert = $this->assertSession();

    $web_user = $this->drupalCreateUser([
      'add social_post entity',
      'edit social_post entity',
      'view social_post entity',
      'delete social_post entity',
      'administer social_post entity',
      'administer social_post display',
      'administer social_post fields',
      'administer social_post form display',
    ]);

    // Anonymous User should not see the link to the listing.
    $assert->pageTextNotContains('Socials');

    $this->drupalLogin($web_user);

    // Web_user user has the right to view listing.
    $assert->linkExists('Socials');

    $this->clickLink('Socials');

    // WebUser can add entity content.
    $assert->linkExists('Add social_post');

    $this->clickLink($this->t('Add social_post'));

    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');

    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';
    $assert->fieldValueEquals('user_id[0][target_id]', $user_ref);

    // Post content, save an instance. Go back to list after saving.
    $edit = [
      'name[0][value]' => 'test name',
      'first_name[0][value]' => 'test first name',
      'role' => 'administrator',
    ];
    $this->submitForm($edit, 'Save');

    // Entity listed.
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    $this->clickLink('test name');

    // Entity shown.
    $assert->pageTextContains('test name');
    $assert->pageTextContains('test first name');
    $assert->pageTextContains('administrator');
    $assert->linkExists('Add social_post');
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    // Delete the entity.
    $this->clickLink('Delete');

    // Confirm deletion.
    $assert->linkExists('Cancel');
    $this->submitForm([], 'Delete');

    // Back to list, must be empty.
    $assert->pageTextNotContains('test name');

    // Settings page.
    $this->drupalGet('admin/structure/social_post_settings');
    $assert->pageTextContains('SocialPost Settings');

    // Make sure the field manipulation links are available.
    $assert->linkExists('Settings');
    $assert->linkExists('Manage fields');
    $assert->linkExists('Manage form display');
    $assert->linkExists('Manage display');
  }

  /**
   * Test all paths exposed by the module, by permission.
   */
  public function testPaths() {
    $assert = $this->assertSession();

    // Generate a social_post so that we can test the paths against it.
    $social_post = SocialPost::create([
      'name' => 'Smith',
      'first_name' => 'Joe',
      'role' => 'administrator',
    ]);
    $social_post->save();

    // Gather the test data.
    $data = $this->providerTestPaths($social_post->id());

    // Run the tests.
    foreach ($data as $datum) {
      // drupalCreateUser() doesn't know what to do with an empty permission
      // array, so we help it out.
      if ($datum[2]) {
        $user = $this->drupalCreateUser([$datum[2]]);
        $this->drupalLogin($user);
      }
      else {
        $user = $this->drupalCreateUser();
        $this->drupalLogin($user);
      }
      $this->drupalGet($datum[1]);
      $assert->statusCodeEquals($datum[0]);
    }
  }

  /**
   * Data provider for testPaths.
   *
   * @param int $social_post_id
   *   The id of an existing SocialPost entity.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Expected response code.
   *   - Path to request.
   *   - Permission for the user.
   */
  protected function providerTestPaths($social_post_id) {
    return [
      [
        200,
        '/social_post/' . $social_post_id,
        'view social_post entity',
      ],
      [
        403,
        '/social_post/' . $social_post_id,
        '',
      ],
      [
        200,
        '/social_post/list',
        'view social_post entity',
      ],
      [
        403,
        '/social_post/list',
        '',
      ],
      [
        200,
        '/social_post/add',
        'add social_post entity',
      ],
      [
        403,
        '/social_post/add',
        '',
      ],
      [
        200,
        '/social_post/' . $social_post_id . '/edit',
        'edit social_post entity',
      ],
      [
        403,
        '/social_post/' . $social_post_id . '/edit',
        '',
      ],
      [
        200,
        '/social_post/' . $social_post_id . '/delete',
        'delete social_post entity',
      ],
      [
        403,
        '/social_post/' . $social_post_id . '/delete',
        '',
      ],
      [
        200,
        'admin/structure/social_post_settings',
        'administer social_post entity',
      ],
      [
        403,
        'admin/structure/social_post_settings',
        '',
      ],
    ];
  }

  /**
   * Test add new fields to the social_post entity.
   */
  public function testAddFields() {
    $web_user = $this->drupalCreateUser([
      'administer social_post entity',
      'administer social_post display',
      'administer social_post fields',
      'administer social_post form display',
    ]);

    $this->drupalLogin($web_user);
    $entity_name = 'social_post';
    $add_field_url = 'admin/structure/' . $entity_name . '_settings/fields/add-field';
    $this->drupalGet($add_field_url);
    $field_name = 'test_name';
    $edit = [
      'new_storage_type' => 'list_string',
      'label' => 'test name',
      'field_name' => $field_name,
    ];

    $this->submitForm($edit, 'Save and continue');
    $expected_path = $this->buildUrl('admin/structure/' . $entity_name . '_settings/fields/' . $entity_name . '.' . $entity_name . '.field_' . $field_name . '/storage');

    // Fetch url without query parameters.
    $current_path = strtok($this->getUrl(), '?');
    $this->assertEquals($expected_path, $current_path);
  }

  /**
   * Ensure admin and users with the right permissions can create social_posts.
   */
  public function testCreateAdminPermission() {
    $assert = $this->assertSession();
    $add_url = Url::fromRoute('socials.social_post_add');

    // Create a SocialPost entity object so that we can query it for it's annotated
    // properties. We don't need to save it.
    /** @var \Drupal\socials\Entity\SocialPost $social_post */
    $social_post = SocialPost::create();

    // Create an admin user and log them in. We use the entity annotation for
    // admin_permission in order to validate it. We also have to add the view
    // list permission because the add form redirects to the list on success.
    $this->drupalLogin($this->drupalCreateUser([
      $social_post->getEntityType()->getAdminPermission(),
      'view social_post entity',
    ]));

    // Post a social_post.
    $edit = [
      'name[0][value]' => 'Test Admin Name',
      'first_name[0][value]' => 'Admin First Name',
      'role' => 'administrator',
    ];
    $this->drupalGet($add_url);
    $this->submitForm($edit, 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Test Admin Name');

    // Create a user with 'add social_post entity' permission. We also have to add
    // the view list permission because the add form redirects to the list on
    // success.
    $this->drupalLogin($this->drupalCreateUser([
      'add social_post entity',
      'view social_post entity',
    ]));

    // Post a social_post.
    $edit = [
      'name[0][value]' => 'Mere Mortal Name',
      'first_name[0][value]' => 'Mortal First Name',
      'role' => 'user',
    ];
    $this->drupalGet($add_url);
    $this->submitForm($edit, 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Mere Mortal Name');

    // Finally, a user who can only view should not be able to get to the add
    // form.
    $this->drupalLogin($this->drupalCreateUser([
      'view social_post entity',
    ]));
    $this->drupalGet($add_url);
    $assert->statusCodeEquals(403);
  }

}
