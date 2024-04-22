<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

class SampleTest extends WPTestCase
{
    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set-up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_factory(): void
    {
        $post = static::factory()->post->create_and_get();

        $this->assertInstanceOf(\WP_Post::class, $post);
    }

    public function test_plugin_active(): void
    {
        $this->assertTrue(is_plugin_active('lifterlms/lifterlms.php'));
    }
}