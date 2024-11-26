<?php

namespace Drupal\Tests\socials\Kernel;

use Drupal\socials\Entity\SocialPost;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test basic CRUD operations for our SocialPost entity type.
 *
 * @group socials
 * @group examples
 *
 * @ingroup socials
 */
class SocialPostTest extends KernelTestBase {

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
    $this->installEntitySchema('socials_social_post');
    $entity = SocialPost::create([
      'name' => 'Name',
      'first_name' => 'First name',
      'user_id' => 0,
      'role' => 'user',
    ]);
    $this->assertNotNull($entity);
    $this->assertEquals(SAVED_NEW, $entity->save());
    $this->assertEquals(SAVED_UPDATED, $entity->set('role', 'administrator')->save());
    $entity_id = $entity->id();
    $this->assertNotEmpty($entity_id);
    $entity->delete();
    $this->assertNull(SocialPost::load($entity_id));
  }

}
