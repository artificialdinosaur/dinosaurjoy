<?php
/**
 * WordPress Post watcher.
 *
 * @package Yoast\YoastSEO\Watchers
 */

namespace Yoast\WP\SEO\Watchers;

use Yoast\WP\SEO\Conditionals\Indexables_Feature_Flag_Conditional;
use Yoast\WP\SEO\Builders\Indexable_Post_Builder;
use Yoast\WP\SEO\Repositories\Indexable_Repository;
use Yoast\WP\SEO\WordPress\Integration;

/**
 * Fills the Indexable according to Post data.
 */
class Indexable_Post_Watcher implements Integration {

	/**
	 * @var \Yoast\WP\SEO\Repositories\Indexable_Repository
	 */
	protected $repository;

	/**
	 * @var \Yoast\WP\SEO\Builders\Indexable_Post_Builder
	 */
	protected $builder;

	/**
	 * @inheritDoc
	 */
	public static function get_conditionals() {
		return [ Indexables_Feature_Flag_Conditional::class ];
	}

	/**
	 * Indexable_Post_Watcher constructor.
	 *
	 * @param \Yoast\WP\SEO\Repositories\Indexable_Repository $repository The repository to use.
	 * @param \Yoast\WP\SEO\Builders\Indexable_Post_Builder   $builder    The post builder to use.
	 */
	public function __construct( Indexable_Repository $repository, Indexable_Post_Builder $builder ) {
		$this->repository = $repository;
		$this->builder    = $builder;
	}

	/**
	 * @inheritDoc
	 */
	public function register_hooks() {
		\add_action( 'wp_insert_post', [ $this, 'build_indexable' ], \PHP_INT_MAX );
		\add_action( 'delete_post', [ $this, 'delete_indexable' ] );
	}

	/**
	 * Deletes the meta when a post is deleted.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function delete_indexable( $post_id ) {
		$indexable = $this->repository->find_by_id_and_type( $post_id, 'post', false );

		if ( ! $indexable ) {
			return;
		}

		$indexable->delete();
	}

	/**
	 * Saves post meta.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function build_indexable( $post_id ) {
		if ( ! $this->is_post_indexable( $post_id ) ) {
			return;
		}

		$indexable = $this->repository->find_by_id_and_type( $post_id, 'post', false );

		// 更多精品WP资源尽在喵容：miaoroom.com
//If we haven't found an existing indexable, create it. Otherwise update it.
		$indexable = ( $indexable === false ) ? $this->repository->create_for_id_and_type( $post_id, 'post' ) : $this->builder->build( $post_id, $indexable );

		$indexable->save();
	}

	/**
	 * Determines if the post can be indexed.
	 *
	 * @param int $post_id Post ID to check.
	 *
	 * @return bool True if the post can be indexed.
	 */
	protected function is_post_indexable( $post_id ) {
		if ( \wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( \wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		return true;
	}
}
