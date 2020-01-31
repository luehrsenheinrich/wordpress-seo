<?php

namespace Yoast\WP\SEO\Tests\Integrations\Watchers;

use Mockery;
use Yoast\WP\SEO\Builders\Indexable_Builder;
use Yoast\WP\SEO\Conditionals\Migrations_Conditional;
use Yoast\WP\SEO\Integrations\Watchers\WPSEO_Titles_Option_Watcher;
use Yoast\WP\SEO\Models\Indexable;
use Yoast\WP\SEO\Repositories\Indexable_Repository;
use Yoast\WP\SEO\Tests\TestCase;

/**
 * Class WPSEO_Titles_Option_Watcher.
 *
 * @group indexables
 * @group watchers
 *
 * @coversDefaultClass \Yoast\WP\SEO\Integrations\Watchers\WPSEO_Titles_Option_Watcher
 * @covers ::<!public>
 *
 * @package Yoast\Tests\Watchers
 */
class WPSEO_Titles_Option_Watcher_Test extends TestCase {

	/**
	 * @var Mockery\MockInterface|Indexable_Repository
	 */
	private $repository_mock;

	/**
	 * @var Mockery\MockInterface|Indexable_Builder
	 */
	private $builder_mock;

	/**
	 * @var WPSEO_Titles_Option_Watcher
	 */
	private $instance;

	public function setUp() {
		$this->repository_mock = Mockery::mock( Indexable_Repository::class );
		$this->builder_mock    = Mockery::mock( Indexable_Builder::class );
		$this->instance        = new WPSEO_Titles_Option_Watcher( $this->repository_mock, $this->builder_mock );

		return parent::setUp();
	}

	/**
	 * Tests if the expected conditionals are in place.
	 *
	 * @covers ::get_conditionals
	 */
	public function test_get_conditionals() {
		$this->assertEquals(
			[ Migrations_Conditional::class ],
			WPSEO_Titles_Option_Watcher::get_conditionals()
		);
	}

	/**
	 * Tests if the expected hooks are registered.
	 *
	 * @covers ::__construct
	 * @covers ::register_hooks
	 */
	public function test_register_hooks() {
		$this->instance->register_hooks();
		$this->assertNotFalse( \has_action( 'update_option_wpseo_titles', [ $this->instance, 'check_ptarchive_option' ] ) );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::check_ptarchive_option
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_update_wpseo_titles_value() {
		$indexable_mock = Mockery::mock( Indexable::class );
		$indexable_mock->expects( 'save' )->once();

		$this->repository_mock->expects( 'find_for_post_type_archive' )->once()->with( 'my-post-type', false )->andReturn( $indexable_mock );
		$this->builder_mock->expects( 'build_for_post_type_archive' )->once()->with( 'my-post-type', $indexable_mock )->andReturn( $indexable_mock );

		$this->instance->check_ptarchive_option( [ 'title-ptarchive-my-post-type' => 'bar' ], [ 'title-ptarchive-my-post-type' => 'baz' ] );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::check_ptarchive_option
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_update_wpseo_titles_value_new() {
		$indexable_mock = Mockery::mock( Indexable::class );
		$indexable_mock->expects( 'save' )->once();

		$this->repository_mock->expects( 'find_for_post_type_archive' )->once()->with( 'my-post-type', false )->andReturn( $indexable_mock );
		$this->builder_mock->expects( 'build_for_post_type_archive' )->once()->with( 'my-post-type', $indexable_mock )->andReturn( $indexable_mock );

		$this->instance->check_ptarchive_option( [], [ 'title-ptarchive-my-post-type' => 'baz' ] );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::check_ptarchive_option
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_update_wpseo_titles_value_switched() {
		$indexable_mock = Mockery::mock( Indexable::class );
		$indexable_mock->expects( 'save' )->once();

		$other_indexable_mock = Mockery::mock( Indexable::class );
		$other_indexable_mock->expects( 'save' )->once();

		$this->repository_mock->expects( 'find_for_post_type_archive' )->once()->with( 'my-post-type', false )->andReturn( $indexable_mock );
		$this->repository_mock->expects( 'find_for_post_type_archive' )->once()->with( 'other-post-type', false )->andReturn( $other_indexable_mock );

		$this->builder_mock->expects( 'build_for_post_type_archive' )->once()->with( 'my-post-type', $indexable_mock )->andReturn( $indexable_mock );
		$this->builder_mock->expects( 'build_for_post_type_archive' )->once()->with( 'other-post-type', $other_indexable_mock )->andReturn( $other_indexable_mock );

		$this->instance->check_ptarchive_option( [ 'title-ptarchive-my-post-type' => 'baz' ], [ 'title-ptarchive-other-post-type' => 'baz' ] );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::check_ptarchive_option
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_update_wpseo_titles_value_same_value() {
		// No assertions made so this will fail if any method is called on our mocks.
		$this->instance->check_ptarchive_option( [ 'title-ptarchive-my-post-type' => 'bar' ], [ 'title-ptarchive-my-post-type' => 'bar' ] );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::check_ptarchive_option
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_update_wpseo_titles_value_without_change() {
		// No assertions made so this will fail if any method is called on our mocks.
		$this->instance->check_ptarchive_option( [ 'other_key' => 'bar' ], [ 'other_key' => 'baz' ] );
	}

	/**
	 * Tests if updating titles works as expected.
	 *
	 * @covers ::__construct
	 * @covers ::build_ptarchive_indexable
	 */
	public function test_build_pt_archive_indexable_without_indexable() {
		$indexable_mock = Mockery::mock( Indexable::class );
		$indexable_mock->expects( 'save' )->once();

		$this->repository_mock->expects( 'find_for_post_type_archive' )->once()->with( 'my-post-type', false )->andReturn( false );
		$this->builder_mock->expects( 'build_for_post_type_archive' )->once()->with( 'my-post-type', false )->andReturn( $indexable_mock );

		$this->instance->build_ptarchive_indexable( 'my-post-type' );
	}
}
