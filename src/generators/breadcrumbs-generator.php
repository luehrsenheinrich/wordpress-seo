<?php
/**
 * Generator object for the breadcrumbs.
 *
 * @package Yoast\YoastSEO\Generators
 */

namespace Yoast\WP\SEO\Generators;

use Yoast\WP\SEO\Context\Meta_Tags_Context;
use Yoast\WP\SEO\Helpers\Current_Page_Helper;
use Yoast\WP\SEO\Helpers\Options_Helper;
use Yoast\WP\SEO\Models\Indexable;
use Yoast\WP\SEO\Generators\Generator_Interface;
use Yoast\WP\SEO\Repositories\Indexable_Repository;

/**
 * Represents the generator class for the Open Graph images.
 */
class Breadcrumbs_Generator implements Generator_Interface {

	/**
	 * The indexable repository.
	 *
	 * @var Indexable_Repository
	 */
	private $repository;

	/**
	 * The options helper.
	 *
	 * @var Options_Helper
	 */
	private $options;

	/**
	 * The current page helper
	 *
	 * @var Current_Page_Helper
	 */
	private $current_page;

	/**
	 * Breadcrumbs_Generator constructor.
	 *
	 * @param Indexable_Repository $repository   The repository.
	 * @param Options_Helper       $options      The options helper.
	 * @param Current_Page_Helper  $current_page The current page helper.
	 */
	public function __construct(
		Indexable_Repository $repository,
		Options_Helper $options,
		Current_Page_Helper $current_page
	) {
		$this->repository   = $repository;
		$this->options      = $options;
		$this->current_page = $current_page;
	}

	/**
	 * Generates the breadcrumbs.
	 *
	 * @param Meta_Tags_Context $context The meta tags context.
	 *
	 * @return array An array of associative arrays that each have a 'text' and a 'url'.
	 */
	public function generate( Meta_Tags_Context $context ) {
		$static_ancestors = [];
		if ( $this->options->get( 'breadcrumbs-home' ) !== '' ) {
			$front_page_id = $this->current_page->get_front_page_id();
			if ( $front_page_id === 0 ) {
				$static_ancestors[] = $this->repository->find_for_home_page();
			}
			else {
				$static_ancestors[] = $this->repository->find_by_id_and_type( $front_page_id, 'post' );
			}
		}
		$page_for_posts = \get_option( 'page_for_posts' );
		if ( $this->should_have_blog_crumb( $page_for_posts ) ) {
			$static_ancestors[] = $this->repository->find_by_id_and_type( $page_for_posts, 'post' );
		}

		// Get all ancestors of the indexable and append itself to get all indexables in the full crumb.
		$indexables   = $this->repository->get_ancestors( $context->indexable );
		$indexables[] = $context->indexable;

		if ( ! empty( $static_ancestors ) ) {
			array_unshift( $indexables, ...$static_ancestors );
		}

		$crumbs = array_map( function ( Indexable $ancestor ) {
			$crumb = [
				'url'  => $ancestor->permalink,
				'text' => $ancestor->breadcrumb_title,
			];

			if ( $ancestor->object_type === 'post' ) {
				$crumb['id'] = $ancestor->object_id;
			}
			if ( $ancestor->object_type === 'post-type-archive' ) {
				$crumb['ptarchive'] = $ancestor->object_sub_type;
			}
			return $crumb;
		}, $indexables );

		/**
		 * Filter: 'wpseo_breadcrumb_links' - Allow the developer to filter the Yoast SEO breadcrumb links, add to them, change order, etc.
		 *
		 * @api array $crumbs The crumbs array.
		 */
		$crumbs = apply_filters( 'wpseo_breadcrumb_links', $crumbs );

		return array_map( function( $link_info, $index ) use ( $crumbs ) {
			/**
			 * Filter: 'wpseo_breadcrumb_single_link_info' - Allow developers to filter the Yoast SEO Breadcrumb link information.
			 *
			 * @api array $link_info The breadcrumb link information.
			 *
			 * @param int $index The index of the breadcrumb in the list.
			 * @param array $crumbs The complete list of breadcrumbs.
			 */
			return apply_filters( 'wpseo_breadcrumb_single_link_info', $link_info, $index, $crumbs );
		}, $crumbs, array_keys( $crumbs ) );
	}

	/**
	 * Returns whether or not a blog crumb should be added.
	 *
	 * @param int $page_for_posts The page for posts ID.
	 *
	 * @return bool Whether or not a blog crumb should be added.
	 */
	protected function should_have_blog_crumb( $page_for_posts ) {
		if ( $this->options->get( 'breadcrumbs-display-blog-page' ) !== true ) {
			return false;
		}

		// When there is no page configured as blog page.
		if ( \get_option( 'show_on_front' ) !== 'page' || ! $page_for_posts ) {
			return false;
		}

		// When the current page is the home page, searchpage or isn't a singular post.
		if ( is_home() || is_search() || ! is_singular( 'post' ) ) {
			return false;
		}

		return true;
	}
}