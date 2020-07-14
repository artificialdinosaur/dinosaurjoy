<?php
/**
 * Extends the free functionality of the XML sitemap class.
 * 
 * @since 3.4.0
 */
 
if ( !class_exists( 'All_in_One_SEO_Pack_Sitemap' ) ) {
	include_once( AIOSEOP_PLUGIN_DIR . "modules/aioseop_sitemap.php" );
}

class All_in_One_SEO_Pack_Sitemap_Pro extends All_in_One_SEO_Pack_Sitemap {

		/**
		 * Initiates the class instance.
		 *
		 * @since 3.4.0
		 */
		public function __construct() {
			$this->name           = __( 'XML Sitemap', 'all-in-one-seo-pack' ); // Human-readable name of the plugin.
			$this->prefix         = 'aiosp_sitemap_';                           // Option prefix.
			$this->file           = __FILE__;                                   // The current file.
			$this->extra_sitemaps = array();
			$this->extra_sitemaps = apply_filters( $this->prefix . 'extra', $this->extra_sitemaps );
			parent::__construct();
		}
    
		/**
		 * Gets the last modified timestamp, priority and frequency for taxonomy terms.
		 *
		 * @since   3.4.0   Check if term has sitemap priority/frequency in termmeta. Renamed function to better reflect purpose.
		 *
		 * @param   array   $terms              The taxonomy terms that need their sitemap meta info to be populated.
		 * @return  array   $populated_terms    The taxonomy terms with their sitemap meta info.
		 */
		public function get_terms_data( $terms ) {
			$populated_terms = array();
			if ( is_array( $terms ) && ! empty( $terms ) ) {

				foreach ( $terms as $term ) {
					$pr_info               = array();
					$pr_info['loc']        = $this->get_term_link( $term, $term->taxonomy );
					$pr_info['lastmod']    = $this->get_tax_term_timestamp( $term );
					$pr_info['priority']   = $this->get_default_priority( 'taxonomies' );
					$pr_info['changefreq'] = $this->get_default_frequency( 'taxonomies' );

					if ( isset( $this->options[ $this->prefix . 'prio_taxonomies' ] ) && 'no' !== $this->options[ $this->prefix . 'prio_taxonomies' ] ) {

						if ( 'sel' !== $this->options[ $this->prefix . 'prio_taxonomies' ] ) {
							$pr_info['priority'] = $this->options[ $this->prefix . 'prio_taxonomies' ];
						} elseif ( 'no' !== $this->options[ $this->prefix . 'prio_taxonomies_' . $term->taxonomy ] ) {
							$pr_info['priority'] = $this->options[ $this->prefix . 'prio_taxonomies_' . $term->taxonomy ];
						}
					}

					if ( isset( $this->options[ $this->prefix . 'freq_taxonomies' ] ) && 'no' !== $this->options[ $this->prefix . 'freq_taxonomies' ] ) {

						if ( 'sel' !== $this->options[ $this->prefix . 'freq_taxonomies' ] ) {
							$pr_info['changefreq'] = $this->options[ $this->prefix . 'freq_taxonomies' ];
						} elseif ( 'no' !== $this->options[ $this->prefix . 'freq_taxonomies_' . $term->taxonomy ] ) {
							$pr_info['changefreq'] = $this->options[ $this->prefix . 'freq_taxonomies_' . $term->taxonomy ];
						}
					}

					$termmeta_prio = get_term_meta( $term->term_id, '_aioseop_sitemap_priority', true );
					$termmeta_freq = get_term_meta( $term->term_id, '_aioseop_sitemap_frequency', true );

					if ( ! empty( $termmeta_prio ) ) {
						$pr_info['priority'] = $termmeta_prio;
					}

					if ( ! empty( $termmeta_freq ) ) {
						$pr_info['changefreq'] = $termmeta_freq;
					}

					$pr_info['image:image'] = $this->get_images_from_term( $term );

					$pr_info['rss'] = array(
						'title'       => $term->name,
						'description' => $term->description,
						'pubDate'     => $this->get_date_for_term( $term ),
					);

					$populated_terms[] = $pr_info;
				}
			}

			return $populated_terms;
        }
        
        /**
		 * Gets the last modified timestamp, priority and frequency for posts.
		 *
		 * @since   3.4.0   Check if post has sitemap priority/frequency in postmeta. Renamed function to better reflect purpose.
		 *
		 * @param           $posts              The posts that need their sitemap meta info to be populated.
		 * @param   bool    $prio_override
		 * @param   bool    $freq_override
		 * @param   string  $linkfunc
		 * @param   string  $type               Type of entity being fetched viz. author, post etc.
		 * @return  array   $populated_posts    The posts with their sitemap meta info.
		 */
		public function get_posts_data( $posts, $prio_override = false, $freq_override = false, $linkfunc = 'get_permalink', $type = 'post' ) {
			$populated_posts = array();
			$args            = array(
				'prio_override' => $prio_override,
				'freq_override' => $freq_override,
				'linkfunc'      => $linkfunc,
			);

			if ( $prio_override && $freq_override ) {
				$stats = 0;
			} else {
				$stats = $this->get_comment_count_stats( $posts );
			}
			if ( is_array( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					// Determine if we check the post for images.
					$is_single    = true;
					$post->filter = 'sample';
					$timestamp    = null;
					if ( 'get_permalink' === $linkfunc ) {
						$url = $this->get_permalink( $post );
					} else {
						$url       = call_user_func( $linkfunc, $post );
						$is_single = false;
					}

					if ( strpos( $url, '__trashed' ) !== false ) {
						// excluded trashed urls.
						continue;
					}

					$date = $post->post_modified_gmt;
					if ( '0000-00-00 00:00:00' === $date ) {
						$date = $post->post_date_gmt;
					}
					if ( '0000-00-00 00:00:00' !== $date ) {
						$timestamp = $date;
						$date      = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $date ) );
					} else {
						$date = 0;
					}

					if ( $prio_override && $freq_override ) {
						$pr_info = array(
							'lastmod'    => $date,
							'changefreq' => null,
							'priority'   => null,
						);
					} else {
						if ( empty( $post->comment_count ) ) {
							$stat = 0;
						} else {
							$stat = $stats;
						}
						if ( ! empty( $stat ) ) {
							$stat['comment_count'] = $post->comment_count;
						}
						$pr_info = $this->get_prio_calc( $date, $stat );
					}

					if ( $freq_override ) {
						$pr_info['changefreq'] = $freq_override;
					}

					if ( $prio_override ) {
						$pr_info['priority'] = $prio_override;
					}

					if ( isset( $this->options[ $this->prefix . 'prio_post' ] ) && 'no' !== $this->options[ $this->prefix . 'prio_post' ] ) {

						if ( 'sel' !== $this->options[ $this->prefix . 'prio_post' ] ) {
							$pr_info['priority'] = $this->options[ $this->prefix . 'prio_post' ];
						} elseif ( 'no' !== $this->options[ $this->prefix . 'prio_post_' . $post->post_type ] ) {
							$pr_info['priority'] = $this->options[ $this->prefix . 'prio_post_' . $post->post_type ];
						}
					}

					if ( isset( $this->options[ $this->prefix . 'freq_post' ] ) && 'no' !== $this->options[ $this->prefix . 'freq_post' ] ) {

						if ( 'sel' !== $this->options[ $this->prefix . 'freq_post' ] ) {
							$pr_info['changefreq'] = $this->options[ $this->prefix . 'freq_post' ];
						} elseif ( 'no' !== $this->options[ $this->prefix . 'freq_post_' . $post->post_type ] ) {
							$pr_info['changefreq'] = $this->options[ $this->prefix . 'freq_post_' . $post->post_type ];
						}
					}

					$postmeta_prio = get_post_meta( $post->ID, '_aioseop_sitemap_priority', true );
					$postmeta_freq = get_post_meta( $post->ID, '_aioseop_sitemap_frequency', true );

					if ( ! empty( $postmeta_prio ) ) {
						$pr_info['priority'] = $postmeta_prio;
					}

					if ( ! empty( $postmeta_freq ) ) {
						$pr_info['changefreq'] = $postmeta_freq;
					}

					$pr_info = array(
						'loc' => $url,
					) + $pr_info; // Prepend loc to	the	array.
					if ( is_float( $pr_info['priority'] ) ) {
						$pr_info['priority'] = sprintf( '%0.1F', $pr_info['priority'] );
					}

					// add the rss specific data.
					if ( $timestamp ) {
						$title = null;
						switch ( $type ) {
							case 'author':
								$title = get_the_author_meta( 'display_name', $key );
								break;
							default:
								$title = get_the_title( $post );
								break;
						}

						// RSS expects the GMT date.
						$timestamp      = mysql2date( 'U', $post->post_modified_gmt );
						$pr_info['rss'] = array(
							'title'       => $title,
							'description' => get_post_field( 'post_excerpt', $post->ID ),
							'pubDate'     => date( 'r', $timestamp ),
							'timestamp  ' => $timestamp,
							'post_type'   => $post->post_type,
						);
					}

					$pr_info['image:image'] = $is_single ? $this->get_images_from_post( $post ) : null;

					$pr_info = apply_filters( $this->prefix . 'prio_item_filter', $pr_info, $post, $args );
					if ( ! empty( $pr_info ) ) {
						$populated_posts[] = $pr_info;
					}
				}
			}

			return $populated_posts;
        }    
        
		/**
		 * Sets the priority and frequency for the static blogpage.
		 *
		 * @since   3.4.0
		 *
		 * @param   array   $links
		 * @return  array   $links
		 */
		protected function get_prio_freq_static_blogpage( $links ) {
			$blogpage_id = (int) get_option( 'page_for_posts' );
			$permalink   = get_permalink( $blogpage_id );

			if ( 0 === $blogpage_id || 'page' !== get_option( 'show_on_front' ) ) {
				return $links;
			}

			$blogpage_index = array_search( $permalink, array_column( $links, 'loc' ) ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_columnFound

			if ( isset( $this->options[ $this->prefix . 'prio_post' ] ) && 'no' !== $this->options[ $this->prefix . 'prio_post' ] ) {

				if ( 'sel' !== $this->options[ $this->prefix . 'prio_post' ] ) {
					$links[ $blogpage_index ]['priority'] = $this->options[ $this->prefix . 'prio_post' ];
				} elseif ( 'no' !== $this->options[ $this->prefix . 'prio_post_' . 'page' ] ) {
					$links[ $blogpage_index ]['priority'] = $this->options[ $this->prefix . 'prio_post_' . 'page' ];
				}
			}

			if ( isset( $this->options[ $this->prefix . 'freq_post' ] ) && 'no' !== $this->options[ $this->prefix . 'freq_post' ] ) {

				if ( 'sel' !== $this->options[ $this->prefix . 'freq_post' ] ) {
					$links[ $blogpage_index ]['changefreq'] = $this->options[ $this->prefix . 'freq_post' ];
				} elseif ( 'no' !== $this->options[ $this->prefix . 'freq_post_' . 'page' ] ) {
					$links[ $blogpage_index ]['changefreq'] = $this->options[ $this->prefix . 'freq_post_' . 'page' ];
				}
			}

			$postmeta_prio = get_post_meta( $blogpage_id, '_aioseop_sitemap_priority', true );
			$postmeta_freq = get_post_meta( $blogpage_id, '_aioseop_sitemap_frequency', true );

			if ( ! empty( $postmeta_prio ) ) {
				$links[ $blogpage_index ]['priority'] = $postmeta_prio;
			}

			if ( ! empty( $postmeta_freq ) ) {
				$links[ $blogpage_index ]['changefreq'] = $postmeta_freq;
			}

			return $links;
		}

}