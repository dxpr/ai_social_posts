<?php

namespace Drupal\Tests\ai_social_posts\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ai_social_posts\Entity\AiSocialPost;

/**
 * Test basic CRUD operations for our AiSocialPost entity type.
 *
 * @group ai_social_posts
 * @group examples
 *
 * @ingroup ai_social_posts
 */
class AiSocialPostTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ai_social_posts', 'options', 'user'];

  /**
   * Basic CRUD operations on a AiSocialPost entity.
   */
  public function testEntity() {
    $this->installEntitySchema('ai_social_post');
    $entity = AiSocialPost::create([
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
    $this->assertNull(AiSocialPost::load($entity_id));
  }

}
