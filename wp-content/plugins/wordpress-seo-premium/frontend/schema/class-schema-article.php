<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Frontend\Schema
 */

/**
 * Returns schema Article data.
 *
 * @since 10.2
 */
class WPSEO_Schema_Article implements WPSEO_Graph_Piece {

	/**
	 * The date helper.
	 *
	 * @var WPSEO_Date_Helper
	 */
	protected $date;

	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	/**
	 * WPSEO_Schema_Article constructor.
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->context = $context;
		$this->date    = new WPSEO_Date_Helper();
	}

	/**
	 * Determines whether or not a piece should be added to the graph.
	 *
	 * @return bool
	 */
	public function is_needed() {
		if ( ! is_singular() ) {
			return false;
		}

		if ( $this->context->site_represents === false ) {
			return false;
		}

		return self::is_article_post_type( get_post_type() );
	}

	/**
	 * Returns Article data.
	 *
	 * @return array $data Article data.
	 */
	public function generate() {
		$post          = get_post( $this->context->id );
		$comment_count = get_comment_count( $this->context->id );
		$data          = [
			'@type'            => 'Article',
			'@id'              => $this->context->canonical . WPSEO_Schema_IDs::ARTICLE_HASH,
			'isPartOf'         => [ '@id' => $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH ],
			'author'           => [ '@id' => WPSEO_Schema_Utils::get_user_schema_id( $post->post_author, $this->context ) ],
			'headline'         => WPSEO_Schema_Utils::get_post_title_with_fallback( $this->context->id ),
			'datePublished'    => $this->date->format( $post->post_date_gmt ),
			'dateModified'     => $this->date->format( $post->post_modified_gmt ),
			'commentCount'     => $comment_count['approved'],
			'mainEntityOfPage' => [ '@id' => $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH ],
		];

		if ( $this->context->site_represents_reference ) {
			$data['publisher'] = $this->context->site_represents_reference;
		}

		$data = $this->add_image( $data );
		$data = $this->add_keywords( $data );
		$data = $this->add_sections( $data );
		$data = WPSEO_Schema_Utils::add_piece_language( $data );

		if ( post_type_supports( $post->post_type, 'comments' ) && $post->comment_status === 'open' ) {
			$data = $this->add_potential_action( $data );
		}

		return $data;
	}

	/**
	 * Determines whether a given post type should have Article schema.
	 *
	 * @param string $post_type Post type to check.
	 *
	 * @return bool True if it has article schema, false if not.
	 */
	public static function is_article_post_type( $post_type = null ) {
		if ( is_null( $post_type ) ) {
			$post_type = get_post_type();
		}

		/**
		 * Filter: 'wpseo_schema_article_post_types' - Allow changing for which post types we output Article schema.
		 *
		 * @api string[] $post_types The post types for which we output Article.
		 */
		$post_types = apply_filters( 'wpseo_schema_article_post_types', [ 'post' ] );

		return in_array( $post_type, $post_types, true );
	}

	/**
	 * Adds tags as keywords, if tags are assigned.
	 *
	 * @param array $data Article data.
	 *
	 * @return array $data Article data.
	 */
	private function add_keywords( $data ) {
		/**
		 * Filter: 'wpseo_schema_article_keywords_taxonomy' - Allow changing the taxonomy used to assign keywords to a post type Article data.
		 *
		 * @api string $taxonomy The chosen taxonomy.
		 */
		$taxonomy = apply_filters( 'wpseo_schema_article_keywords_taxonomy', 'post_tag' );

		return $this->add_terms( $data, 'keywords', $taxonomy );
	}

	/**
	 * Adds categories as sections, if categories are assigned.
	 *
	 * @param array $data Article data.
	 *
	 * @return array $data Article data.
	 */
	private function add_sections( $data ) {
		/**
		 * Filter: 'wpseo_schema_article_sections_taxonomy' - Allow changing the taxonomy used to assign keywords to a post type Article data.
		 *
		 * @api string $taxonomy The chosen taxonomy.
		 */
		$taxonomy = apply_filters( 'wpseo_schema_article_sections_taxonomy', 'category' );

		return $this->add_terms( $data, 'articleSection', $taxonomy );
	}

	/**
	 * Adds a term or multiple terms, comma separated, to a field.
	 *
	 * @param array  $data     Article data.
	 * @param string $key      The key in data to save the terms in.
	 * @param string $taxonomy The taxonomy to retrieve the terms from.
	 *
	 * @return mixed array $data Article data.
	 */
	private function add_terms( $data, $key, $taxonomy ) {
		$terms = get_the_terms( $this->context->id, $taxonomy );
		if ( is_array( $terms ) ) {
			$keywords = [];
			foreach ( $terms as $term ) {
				// 更多精品WP资源尽在喵容：miaoroom.com
//We are checking against the WordPress internal translation.
				// 更多精品WP资源尽在喵容：miaoroom.com
//@codingStandardsIgnoreLine
				if ( $term->name !== __( 'Uncategorized', 'default' ) ) {
					$keywords[] = $term->name;
				}
			}
			$data[ $key ] = implode( ',', $keywords );
		}

		return $data;
	}

	/**
	 * Adds an image node if the post has a featured image.
	 *
	 * @param array $data The Article data.
	 *
	 * @return array $data The Article data.
	 */
	private function add_image( $data ) {
		if ( $this->context->has_image ) {
			$data['image'] = [
				'@id' => $this->context->canonical . WPSEO_Schema_IDs::PRIMARY_IMAGE_HASH,
			];
		}

		return $data;
	}

	/**
	 * Adds the potential action JSON LD code to an Article Schema piece.
	 *
	 * @param array $data The Article data array.
	 *
	 * @return array $data
	 */
	private function add_potential_action( $data ) {
		/**
		 * Filter: 'wpseo_schema_article_potential_action_target' - Allows filtering of the schema Article potentialAction target.
		 *
		 * @api array $targets The URLs for the Article potentialAction target.
		 */
		$targets = apply_filters( 'wpseo_schema_article_potential_action_target', [ $this->context->canonical . '#respond' ] );

		$data['potentialAction'][] = [
			'@type'  => 'CommentAction',
			'name'   => 'Comment',
			'target' => $targets,
		];

		return $data;
	}
}
