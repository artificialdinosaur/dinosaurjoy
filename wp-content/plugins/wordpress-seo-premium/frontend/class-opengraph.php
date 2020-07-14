<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Frontend
 */

/**
 * This code adds the OpenGraph output.
 */
class WPSEO_OpenGraph {

	/**
	 * The date helper.
	 *
	 * @var WPSEO_Date_Helper
	 */
	protected $date;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->date = new WPSEO_Date_Helper();

		if ( isset( $GLOBALS['fb_ver'] ) || class_exists( 'Facebook_Loader', false ) ) {
			add_filter( 'fb_meta_tags', [ $this, 'facebook_filter' ], 10, 1 );
		}
		else {
			add_action( 'wpseo_opengraph', [ $this, 'locale' ], 1 );
			add_action( 'wpseo_opengraph', [ $this, 'type' ], 5 );
			add_action( 'wpseo_opengraph', [ $this, 'og_title' ], 10 );
			add_action( 'wpseo_opengraph', [ $this, 'app_id' ], 20 );
			add_action( 'wpseo_opengraph', [ $this, 'description' ], 11 );
			add_action( 'wpseo_opengraph', [ $this, 'url' ], 12 );
			add_action( 'wpseo_opengraph', [ $this, 'site_name' ], 13 );
			add_action( 'wpseo_opengraph', [ $this, 'website_facebook' ], 14 );
			if ( is_singular() && ! is_front_page() ) {
				add_action( 'wpseo_opengraph', [ $this, 'article_author_facebook' ], 15 );
				add_action( 'wpseo_opengraph', [ $this, 'tags' ], 16 );
				add_action( 'wpseo_opengraph', [ $this, 'category' ], 17 );
				add_action( 'wpseo_opengraph', [ $this, 'publish_date' ], 19 );
			}

			add_action( 'wpseo_opengraph', [ $this, 'image' ], 30 );
		}
		add_filter( 'jetpack_enable_open_graph', '__return_false' );
		add_action( 'wpseo_head', [ $this, 'opengraph' ], 30 );
	}

	/**
	 * Main OpenGraph output.
	 */
	public function opengraph() {
		wp_reset_query();
		/**
		 * Action: 'wpseo_opengraph' - Hook to add all Facebook OpenGraph output to so they're close together.
		 */
		do_action( 'wpseo_opengraph' );
	}

	/**
	 * Internal function to output FB tags. This also adds an output filter to each bit of output based on the property.
	 *
	 * @param string $property Property attribute value.
	 * @param string $content  Content attribute value.
	 *
	 * @return boolean
	 */
	public function og_tag( $property, $content ) {
		$og_property = str_replace( ':', '_', $property );
		/**
		 * Filter: 'wpseo_og_' . $og_property - Allow developers to change the content of specific OG meta tags.
		 *
		 * @api string $content The content of the property.
		 */
		$content = apply_filters( 'wpseo_og_' . $og_property, $content );
		if ( empty( $content ) ) {
			return false;
		}

		echo '<meta property="', esc_attr( $property ), '" content="', esc_attr( $content ), '" />', "\n";

		return true;
	}

	/**
	 * Filter the Facebook plugins metadata.
	 *
	 * @param array $meta_tags The array to fix.
	 *
	 * @return array $meta_tags
	 */
	public function facebook_filter( $meta_tags ) {
		$meta_tags['http://ogp.me/ns#type']  = $this->type( false );
		$meta_tags['http://ogp.me/ns#title'] = $this->og_title( false );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Filter the locale too because the Facebook plugin locale code is not as good as ours.
		$meta_tags['http://ogp.me/ns#locale'] = $this->locale( false );

		$ogdesc = $this->description( false );
		if ( ! empty( $ogdesc ) ) {
			$meta_tags['http://ogp.me/ns#description'] = $ogdesc;
		}

		return $meta_tags;
	}

	/**
	 * Outputs the authors FB page.
	 *
	 * @link https://developers.facebook.com/blog/post/2013/06/19/platform-updates--new-open-graph-tags-for-media-publishers-and-more/
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean
	 */
	public function article_author_facebook() {
		if ( ! is_singular() ) {
			return false;
		}

		/**
		 * Filter: 'wpseo_opengraph_author_facebook' - Allow developers to filter the Yoast SEO post authors facebook profile URL.
		 *
		 * @api bool|string $unsigned The Facebook author URL, return false to disable.
		 */
		$facebook = apply_filters( 'wpseo_opengraph_author_facebook', get_the_author_meta( 'facebook', $GLOBALS['post']->post_author ) );

		if ( $facebook && ( is_string( $facebook ) && $facebook !== '' ) ) {
			$this->og_tag( 'article:author', $facebook );

			return true;
		}

		return false;
	}

	/**
	 * Outputs the websites FB page.
	 *
	 * @link https://developers.facebook.com/blog/post/2013/06/19/platform-updates--new-open-graph-tags-for-media-publishers-and-more/
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean
	 */
	public function website_facebook() {

		if ( 'article' === $this->type( false ) && WPSEO_Options::get( 'facebook_site', '' ) !== '' ) {
			$this->og_tag( 'article:publisher', WPSEO_Options::get( 'facebook_site' ) );

			return true;
		}

		return false;
	}

	/**
	 * Outputs the SEO title as OpenGraph title.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @param bool $echo Whether or not to echo the output.
	 *
	 * @return string|boolean
	 */
	public function og_title( $echo = true ) {

		$frontend = WPSEO_Frontend::get_instance();

		if ( WPSEO_Frontend_Page_Type::is_simple_page() ) {
			$post_id = WPSEO_Frontend_Page_Type::get_simple_page_id();
			$post    = get_post( $post_id );
			$title   = WPSEO_Meta::get_value( 'opengraph-title', $post_id );

			if ( $title === '' ) {
				$title = $frontend->title( '' );
			}
			else {
				// 更多精品WP资源尽在喵容：miaoroom.com
//Replace Yoast SEO Variables.
				$title = wpseo_replace_vars( $title, $post );
			}
		}
		elseif ( is_front_page() ) {
			$title = ( WPSEO_Options::get( 'og_frontpage_title', '' ) !== '' ) ? WPSEO_Options::get( 'og_frontpage_title' ) : $frontend->title( '' );
		}
		elseif ( is_category() || is_tax() || is_tag() ) {
			$title = WPSEO_Taxonomy_Meta::get_meta_without_term( 'opengraph-title' );
			if ( $title === '' ) {
				$title = $frontend->title( '' );
			}
			else {
				// 更多精品WP资源尽在喵容：miaoroom.com
//Replace Yoast SEO Variables.
				$title = wpseo_replace_vars( $title, $GLOBALS['wp_query']->get_queried_object() );
			}
		}
		else {
			$title = $frontend->title( '' );
		}

		/**
		 * Filter: 'wpseo_opengraph_title' - Allow changing the title specifically for OpenGraph.
		 *
		 * @api string $unsigned The title string.
		 */
		$title = trim( apply_filters( 'wpseo_opengraph_title', $title ) );

		if ( is_string( $title ) && $title !== '' ) {
			if ( $echo !== false ) {
				$this->og_tag( 'og:title', $title );

				return true;
			}
		}

		if ( $echo === false ) {
			return $title;
		}

		return false;
	}

	/**
	 * Outputs the canonical URL as OpenGraph URL, which consolidates likes and shares.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean
	 */
	public function url() {
		$url         = WPSEO_Frontend::get_instance()->canonical( false, false );
		$unpaged_url = WPSEO_Frontend::get_instance()->canonical( false, true );

		/*
		 * If the unpaged URL is the same as the normal URL but just with pagination added, use that.
		 * This makes sure we always use the unpaged URL when we can, but doesn't break for overridden canonicals.
		 */
		if ( ! empty( $unpaged_url ) && is_string( $unpaged_url ) && strpos( $url, $unpaged_url ) === 0 ) {
			$url = $unpaged_url;
		}

		/**
		 * Filter: 'wpseo_opengraph_url' - Allow changing the OpenGraph URL.
		 *
		 * @api string $unsigned Canonical URL.
		 */
		$url = urldecode( apply_filters( 'wpseo_opengraph_url', $url ) );

		if ( is_string( $url ) && $url !== '' ) {
			$this->og_tag( 'og:url', esc_url( $url ) );

			return true;
		}

		return false;
	}

	/**
	 * Output the locale, doing some conversions to make sure the proper Facebook locale is outputted.
	 *
	 * Last update/compare with FB list done on 2015-03-16 by Rarst.
	 *
	 * @link http://www.facebook.com/translations/FacebookLocales.xml for the list of supported locales.
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @param bool $echo Whether to echo or return the locale.
	 *
	 * @return string $locale
	 */
	public function locale( $echo = true ) {
		/**
		 * Filter: 'wpseo_locale' - Allow changing the locale output.
		 *
		 * @api string $unsigned Locale string.
		 */
		$locale = apply_filters( 'wpseo_locale', get_locale() );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Catch some weird locales served out by WP that are not easily doubled up.
		$fix_locales = [
			'ca' => 'ca_ES',
			'en' => 'en_US',
			'el' => 'el_GR',
			'et' => 'et_EE',
			'ja' => 'ja_JP',
			'sq' => 'sq_AL',
			'uk' => 'uk_UA',
			'vi' => 'vi_VN',
			'zh' => 'zh_CN',
		];

		if ( isset( $fix_locales[ $locale ] ) ) {
			$locale = $fix_locales[ $locale ];
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//Convert locales like "es" to "es_ES", in case that works for the given locale (sometimes it does).
		if ( strlen( $locale ) === 2 ) {
			$locale = strtolower( $locale ) . '_' . strtoupper( $locale );
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//These are the locales FB supports.
		$fb_valid_fb_locales = [
			'af_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Afrikaans.
			'ak_GH', // 更多精品WP资源尽在喵容：miaoroom.com
//Akan.
			'am_ET', // 更多精品WP资源尽在喵容：miaoroom.com
//Amharic.
			'ar_AR', // 更多精品WP资源尽在喵容：miaoroom.com
//Arabic.
			'as_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Assamese.
			'ay_BO', // 更多精品WP资源尽在喵容：miaoroom.com
//Aymara.
			'az_AZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Azerbaijani.
			'be_BY', // 更多精品WP资源尽在喵容：miaoroom.com
//Belarusian.
			'bg_BG', // 更多精品WP资源尽在喵容：miaoroom.com
//Bulgarian.
			'bp_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Bhojpuri.
			'bn_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Bengali.
			'br_FR', // 更多精品WP资源尽在喵容：miaoroom.com
//Breton.
			'bs_BA', // 更多精品WP资源尽在喵容：miaoroom.com
//Bosnian.
			'ca_ES', // 更多精品WP资源尽在喵容：miaoroom.com
//Catalan.
			'cb_IQ', // 更多精品WP资源尽在喵容：miaoroom.com
//Sorani Kurdish.
			'ck_US', // 更多精品WP资源尽在喵容：miaoroom.com
//Cherokee.
			'co_FR', // 更多精品WP资源尽在喵容：miaoroom.com
//Corsican.
			'cs_CZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Czech.
			'cx_PH', // 更多精品WP资源尽在喵容：miaoroom.com
//Cebuano.
			'cy_GB', // 更多精品WP资源尽在喵容：miaoroom.com
//Welsh.
			'da_DK', // 更多精品WP资源尽在喵容：miaoroom.com
//Danish.
			'de_DE', // 更多精品WP资源尽在喵容：miaoroom.com
//German.
			'el_GR', // 更多精品WP资源尽在喵容：miaoroom.com
//Greek.
			'en_GB', // 更多精品WP资源尽在喵容：miaoroom.com
//English (UK).
			'en_PI', // 更多精品WP资源尽在喵容：miaoroom.com
//English (Pirate).
			'en_UD', // 更多精品WP资源尽在喵容：miaoroom.com
//English (Upside Down).
			'en_US', // 更多精品WP资源尽在喵容：miaoroom.com
//English (US).
			'em_ZM',
			'eo_EO', // 更多精品WP资源尽在喵容：miaoroom.com
//Esperanto.
			'es_ES', // 更多精品WP资源尽在喵容：miaoroom.com
//Spanish (Spain).
			'es_LA', // 更多精品WP资源尽在喵容：miaoroom.com
//Spanish.
			'es_MX', // 更多精品WP资源尽在喵容：miaoroom.com
//Spanish (Mexico).
			'et_EE', // 更多精品WP资源尽在喵容：miaoroom.com
//Estonian.
			'eu_ES', // 更多精品WP资源尽在喵容：miaoroom.com
//Basque.
			'fa_IR', // 更多精品WP资源尽在喵容：miaoroom.com
//Persian.
			'fb_LT', // 更多精品WP资源尽在喵容：miaoroom.com
//Leet Speak.
			'ff_NG', // 更多精品WP资源尽在喵容：miaoroom.com
//Fulah.
			'fi_FI', // 更多精品WP资源尽在喵容：miaoroom.com
//Finnish.
			'fo_FO', // 更多精品WP资源尽在喵容：miaoroom.com
//Faroese.
			'fr_CA', // 更多精品WP资源尽在喵容：miaoroom.com
//French (Canada).
			'fr_FR', // 更多精品WP资源尽在喵容：miaoroom.com
//French (France).
			'fy_NL', // 更多精品WP资源尽在喵容：miaoroom.com
//Frisian.
			'ga_IE', // 更多精品WP资源尽在喵容：miaoroom.com
//Irish.
			'gl_ES', // 更多精品WP资源尽在喵容：miaoroom.com
//Galician.
			'gn_PY', // 更多精品WP资源尽在喵容：miaoroom.com
//Guarani.
			'gu_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Gujarati.
			'gx_GR', // 更多精品WP资源尽在喵容：miaoroom.com
//Classical Greek.
			'ha_NG', // 更多精品WP资源尽在喵容：miaoroom.com
//Hausa.
			'he_IL', // 更多精品WP资源尽在喵容：miaoroom.com
//Hebrew.
			'hi_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Hindi.
			'hr_HR', // 更多精品WP资源尽在喵容：miaoroom.com
//Croatian.
			'hu_HU', // 更多精品WP资源尽在喵容：miaoroom.com
//Hungarian.
			'ht_HT', // 更多精品WP资源尽在喵容：miaoroom.com
//Haitian Creole.
			'hy_AM', // 更多精品WP资源尽在喵容：miaoroom.com
//Armenian.
			'id_ID', // 更多精品WP资源尽在喵容：miaoroom.com
//Indonesian.
			'ig_NG', // 更多精品WP资源尽在喵容：miaoroom.com
//Igbo.
			'is_IS', // 更多精品WP资源尽在喵容：miaoroom.com
//Icelandic.
			'it_IT', // 更多精品WP资源尽在喵容：miaoroom.com
//Italian.
			'ik_US',
			'iu_CA',
			'ja_JP', // 更多精品WP资源尽在喵容：miaoroom.com
//Japanese.
			'ja_KS', // 更多精品WP资源尽在喵容：miaoroom.com
//Japanese (Kansai).
			'jv_ID', // 更多精品WP资源尽在喵容：miaoroom.com
//Javanese.
			'ka_GE', // 更多精品WP资源尽在喵容：miaoroom.com
//Georgian.
			'kk_KZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Kazakh.
			'km_KH', // 更多精品WP资源尽在喵容：miaoroom.com
//Khmer.
			'kn_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Kannada.
			'ko_KR', // 更多精品WP资源尽在喵容：miaoroom.com
//Korean.
			'ks_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Kashmiri.
			'ku_TR', // 更多精品WP资源尽在喵容：miaoroom.com
//Kurdish (Kurmanji).
			'ky_KG', // 更多精品WP资源尽在喵容：miaoroom.com
//Kyrgyz.
			'la_VA', // 更多精品WP资源尽在喵容：miaoroom.com
//Latin.
			'lg_UG', // 更多精品WP资源尽在喵容：miaoroom.com
//Ganda.
			'li_NL', // 更多精品WP资源尽在喵容：miaoroom.com
//Limburgish.
			'ln_CD', // 更多精品WP资源尽在喵容：miaoroom.com
//Lingala.
			'lo_LA', // 更多精品WP资源尽在喵容：miaoroom.com
//Lao.
			'lt_LT', // 更多精品WP资源尽在喵容：miaoroom.com
//Lithuanian.
			'lv_LV', // 更多精品WP资源尽在喵容：miaoroom.com
//Latvian.
			'mg_MG', // 更多精品WP资源尽在喵容：miaoroom.com
//Malagasy.
			'mi_NZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Maori.
			'mk_MK', // 更多精品WP资源尽在喵容：miaoroom.com
//Macedonian.
			'ml_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Malayalam.
			'mn_MN', // 更多精品WP资源尽在喵容：miaoroom.com
//Mongolian.
			'mr_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Marathi.
			'ms_MY', // 更多精品WP资源尽在喵容：miaoroom.com
//Malay.
			'mt_MT', // 更多精品WP资源尽在喵容：miaoroom.com
//Maltese.
			'my_MM', // 更多精品WP资源尽在喵容：miaoroom.com
//Burmese.
			'nb_NO', // 更多精品WP资源尽在喵容：miaoroom.com
//Norwegian (bokmal).
			'nd_ZW', // 更多精品WP资源尽在喵容：miaoroom.com
//Ndebele.
			'ne_NP', // 更多精品WP资源尽在喵容：miaoroom.com
//Nepali.
			'nl_BE', // 更多精品WP资源尽在喵容：miaoroom.com
//Dutch (Belgie).
			'nl_NL', // 更多精品WP资源尽在喵容：miaoroom.com
//Dutch.
			'nn_NO', // 更多精品WP资源尽在喵容：miaoroom.com
//Norwegian (nynorsk).
			'nr_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Southern Ndebele.
			'ns_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Northern Sotho.
			'ny_MW', // 更多精品WP资源尽在喵容：miaoroom.com
//Chewa.
			'om_ET', // 更多精品WP资源尽在喵容：miaoroom.com
//Oromo.
			'or_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Oriya.
			'pa_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Punjabi.
			'pl_PL', // 更多精品WP资源尽在喵容：miaoroom.com
//Polish.
			'ps_AF', // 更多精品WP资源尽在喵容：miaoroom.com
//Pashto.
			'pt_BR', // 更多精品WP资源尽在喵容：miaoroom.com
//Portuguese (Brazil).
			'pt_PT', // 更多精品WP资源尽在喵容：miaoroom.com
//Portuguese (Portugal).
			'qc_GT', // 更多精品WP资源尽在喵容：miaoroom.com
//Quiché.
			'qu_PE', // 更多精品WP资源尽在喵容：miaoroom.com
//Quechua.
			'qr_GR',
			'qz_MM', // 更多精品WP资源尽在喵容：miaoroom.com
//Burmese (Zawgyi).
			'rm_CH', // 更多精品WP资源尽在喵容：miaoroom.com
//Romansh.
			'ro_RO', // 更多精品WP资源尽在喵容：miaoroom.com
//Romanian.
			'ru_RU', // 更多精品WP资源尽在喵容：miaoroom.com
//Russian.
			'rw_RW', // 更多精品WP资源尽在喵容：miaoroom.com
//Kinyarwanda.
			'sa_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Sanskrit.
			'sc_IT', // 更多精品WP资源尽在喵容：miaoroom.com
//Sardinian.
			'se_NO', // 更多精品WP资源尽在喵容：miaoroom.com
//Northern Sami.
			'si_LK', // 更多精品WP资源尽在喵容：miaoroom.com
//Sinhala.
			'su_ID', // 更多精品WP资源尽在喵容：miaoroom.com
//Sundanese.
			'sk_SK', // 更多精品WP资源尽在喵容：miaoroom.com
//Slovak.
			'sl_SI', // 更多精品WP资源尽在喵容：miaoroom.com
//Slovenian.
			'sn_ZW', // 更多精品WP资源尽在喵容：miaoroom.com
//Shona.
			'so_SO', // 更多精品WP资源尽在喵容：miaoroom.com
//Somali.
			'sq_AL', // 更多精品WP资源尽在喵容：miaoroom.com
//Albanian.
			'sr_RS', // 更多精品WP资源尽在喵容：miaoroom.com
//Serbian.
			'ss_SZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Swazi.
			'st_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Southern Sotho.
			'sv_SE', // 更多精品WP资源尽在喵容：miaoroom.com
//Swedish.
			'sw_KE', // 更多精品WP资源尽在喵容：miaoroom.com
//Swahili.
			'sy_SY', // 更多精品WP资源尽在喵容：miaoroom.com
//Syriac.
			'sz_PL', // 更多精品WP资源尽在喵容：miaoroom.com
//Silesian.
			'ta_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Tamil.
			'te_IN', // 更多精品WP资源尽在喵容：miaoroom.com
//Telugu.
			'tg_TJ', // 更多精品WP资源尽在喵容：miaoroom.com
//Tajik.
			'th_TH', // 更多精品WP资源尽在喵容：miaoroom.com
//Thai.
			'tk_TM', // 更多精品WP资源尽在喵容：miaoroom.com
//Turkmen.
			'tl_PH', // 更多精品WP资源尽在喵容：miaoroom.com
//Filipino.
			'tl_ST', // 更多精品WP资源尽在喵容：miaoroom.com
//Klingon.
			'tn_BW', // 更多精品WP资源尽在喵容：miaoroom.com
//Tswana.
			'tr_TR', // 更多精品WP资源尽在喵容：miaoroom.com
//Turkish.
			'ts_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Tsonga.
			'tt_RU', // 更多精品WP资源尽在喵容：miaoroom.com
//Tatar.
			'tz_MA', // 更多精品WP资源尽在喵容：miaoroom.com
//Tamazight.
			'uk_UA', // 更多精品WP资源尽在喵容：miaoroom.com
//Ukrainian.
			'ur_PK', // 更多精品WP资源尽在喵容：miaoroom.com
//Urdu.
			'uz_UZ', // 更多精品WP资源尽在喵容：miaoroom.com
//Uzbek.
			've_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Venda.
			'vi_VN', // 更多精品WP资源尽在喵容：miaoroom.com
//Vietnamese.
			'wo_SN', // 更多精品WP资源尽在喵容：miaoroom.com
//Wolof.
			'xh_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Xhosa.
			'yi_DE', // 更多精品WP资源尽在喵容：miaoroom.com
//Yiddish.
			'yo_NG', // 更多精品WP资源尽在喵容：miaoroom.com
//Yoruba.
			'zh_CN', // 更多精品WP资源尽在喵容：miaoroom.com
//Simplified Chinese (China).
			'zh_HK', // 更多精品WP资源尽在喵容：miaoroom.com
//Traditional Chinese (Hong Kong).
			'zh_TW', // 更多精品WP资源尽在喵容：miaoroom.com
//Traditional Chinese (Taiwan).
			'zu_ZA', // 更多精品WP资源尽在喵容：miaoroom.com
//Zulu.
			'zz_TR', // 更多精品WP资源尽在喵容：miaoroom.com
//Zazaki.
		];

		// 更多精品WP资源尽在喵容：miaoroom.com
//Check to see if the locale is a valid FB one, if not, use en_US as a fallback.
		if ( ! in_array( $locale, $fb_valid_fb_locales, true ) ) {
			$locale = strtolower( substr( $locale, 0, 2 ) ) . '_' . strtoupper( substr( $locale, 0, 2 ) );
			if ( ! in_array( $locale, $fb_valid_fb_locales, true ) ) {
				$locale = 'en_US';
			}
		}

		if ( $echo !== false ) {
			$this->og_tag( 'og:locale', $locale );
		}

		return $locale;
	}

	/**
	 * Output the OpenGraph type.
	 *
	 * @param boolean $echo Whether to echo or return the type.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/object/
	 *
	 * @return string $type
	 */
	public function type( $echo = true ) {

		if ( is_front_page() || is_home() ) {
			$type = 'website';
		}
		elseif ( is_singular() ) {

			// 更多精品WP资源尽在喵容：miaoroom.com
//This'll usually only be changed by plugins right now.
			$type = WPSEO_Meta::get_value( 'og_type' );

			if ( $type === '' ) {
				$type = 'article';
			}
		}
		else {
			// 更多精品WP资源尽在喵容：miaoroom.com
//We use "object" for archives etc. as article doesn't apply there.
			$type = 'object';
		}

		/**
		 * Filter: 'wpseo_opengraph_type' - Allow changing the OpenGraph type of the page.
		 *
		 * @api string $type The OpenGraph type string.
		 */
		$type = apply_filters( 'wpseo_opengraph_type', $type );

		if ( is_string( $type ) && $type !== '' ) {
			if ( $echo !== false ) {
				$this->og_tag( 'og:type', $type );
			}
			else {
				return $type;
			}
		}

		return '';
	}

	/**
	 * Create new WPSEO_OpenGraph_Image class and get the images to set the og:image.
	 *
	 * @param string|bool $image Optional. Image URL.
	 *
	 * @return void
	 */
	public function image( $image = false ) {
		$opengraph_image = new WPSEO_OpenGraph_Image( $image, $this );
		$opengraph_image->show();
	}

	/**
	 * Output the OpenGraph description, specific OG description first, if not, grab the meta description.
	 *
	 * @param bool $echo Whether to echo or return the description.
	 *
	 * @return string $ogdesc
	 */
	public function description( $echo = true ) {
		$ogdesc   = '';
		$frontend = WPSEO_Frontend::get_instance();

		if ( is_front_page() ) {
			if ( WPSEO_Options::get( 'og_frontpage_desc', '' ) !== '' ) {
				$ogdesc = wpseo_replace_vars( WPSEO_Options::get( 'og_frontpage_desc' ), null );
			}
			else {
				$ogdesc = $frontend->metadesc( false );
			}
		}

		if ( WPSEO_Frontend_Page_Type::is_simple_page() ) {
			$post_id = WPSEO_Frontend_Page_Type::get_simple_page_id();
			$post    = get_post( $post_id );
			$ogdesc  = WPSEO_Meta::get_value( 'opengraph-description', $post_id );

			// 更多精品WP资源尽在喵容：miaoroom.com
//Replace Yoast SEO Variables.
			$ogdesc = wpseo_replace_vars( $ogdesc, $post );

			// 更多精品WP资源尽在喵容：miaoroom.com
//Use metadesc if $ogdesc is empty.
			if ( $ogdesc === '' ) {
				$ogdesc = $frontend->metadesc( false );
			}

			// 更多精品WP资源尽在喵容：miaoroom.com
//Tag og:description is still blank so grab it from get_the_excerpt().
			if ( ! is_string( $ogdesc ) || ( is_string( $ogdesc ) && $ogdesc === '' ) ) {
				$ogdesc = str_replace( '[&hellip;]', '&hellip;', wp_strip_all_tags( get_the_excerpt() ) );
			}
		}

		if ( is_author() ) {
			$ogdesc = $frontend->metadesc( false );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$ogdesc = WPSEO_Taxonomy_Meta::get_meta_without_term( 'opengraph-description' );
			if ( $ogdesc === '' ) {
				$ogdesc = $frontend->metadesc( false );
			}

			if ( $ogdesc === '' ) {
				$ogdesc = wp_strip_all_tags( term_description() );
			}

			if ( $ogdesc === '' ) {
				$ogdesc = WPSEO_Taxonomy_Meta::get_meta_without_term( 'desc' );
			}
			$ogdesc = wpseo_replace_vars( $ogdesc, get_queried_object() );
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//Strip shortcodes if any.
		$ogdesc = strip_shortcodes( $ogdesc );

		/**
		 * Filter: 'wpseo_opengraph_desc' - Allow changing the OpenGraph description.
		 *
		 * @api string $ogdesc The description string.
		 */
		$ogdesc = trim( apply_filters( 'wpseo_opengraph_desc', $ogdesc ) );

		if ( is_string( $ogdesc ) && $ogdesc !== '' ) {
			if ( $echo !== false ) {
				$this->og_tag( 'og:description', $ogdesc );
			}
		}

		return $ogdesc;
	}

	/**
	 * Output the site name straight from the blog info.
	 */
	public function site_name() {
		/**
		 * Filter: 'wpseo_opengraph_site_name' - Allow changing the OpenGraph site name.
		 *
		 * @api string $unsigned Blog name string.
		 */
		$name = apply_filters( 'wpseo_opengraph_site_name', get_bloginfo( 'name' ) );
		if ( is_string( $name ) && $name !== '' ) {
			$this->og_tag( 'og:site_name', $name );
		}
	}

	/**
	 * Output the article tags as article:tag tags.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean
	 */
	public function tags() {
		if ( ! is_singular() ) {
			return false;
		}

		$tags = get_the_tags();
		if ( ! is_wp_error( $tags ) && ( is_array( $tags ) && $tags !== [] ) ) {

			foreach ( $tags as $tag ) {
				$this->og_tag( 'article:tag', $tag->name );
			}

			return true;
		}

		return false;
	}

	/**
	 * Output the article category as an article:section tag.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean;
	 */
	public function category() {

		if ( ! is_singular() ) {
			return false;
		}

		$post = get_post();
		if ( ! $post ) {
			return false;
		}

		$primary_term     = new WPSEO_Primary_Term( 'category', $post->ID );
		$primary_category = $primary_term->get_primary_term();

		if ( $primary_category ) {
			// 更多精品WP资源尽在喵容：miaoroom.com
//We can only show one section here, so we take the first one.
			$this->og_tag( 'article:section', get_cat_name( $primary_category ) );

			return true;
		}

		$terms = get_the_category();

		if ( ! is_wp_error( $terms ) && is_array( $terms ) && ! empty( $terms ) ) {
			// 更多精品WP资源尽在喵容：miaoroom.com
//We can only show one section here, so we take the first one.
			$term = reset( $terms );
			$this->og_tag( 'article:section', $term->name );
			return true;
		}

		return false;
	}

	/**
	 * Output the article publish and last modification date.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return boolean;
	 */
	public function publish_date() {

		if ( ! is_singular( 'post' ) ) {
			/**
			 * Filter: 'wpseo_opengraph_show_publish_date' - Allow showing publication date for other post types.
			 *
			 * @api bool $unsigned Whether or not to show publish date.
			 *
			 * @param string $post_type The current URL's post type.
			 */
			if ( false === apply_filters( 'wpseo_opengraph_show_publish_date', false, get_post_type() ) ) {
				return false;
			}
		}

		$post = get_post();

		$pub = $this->date->format( $post->post_date_gmt );
		$this->og_tag( 'article:published_time', $pub );

		$mod = $this->date->format( $post->post_modified_gmt );
		if ( $mod !== $pub ) {
			$this->og_tag( 'article:modified_time', $mod );
			$this->og_tag( 'og:updated_time', $mod );
		}

		return true;
	}

	/**
	 * Outputs the Facebook app_id.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return void
	 */
	public function app_id() {
		$app_id = WPSEO_Options::get( 'fbadminapp', '' );
		if ( $app_id !== '' ) {
			$this->og_tag( 'fb:app_id', $app_id );
		}
	}

	/* ********************* DEPRECATED METHODS ********************* */

	/**
	 * Outputs the site owner.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return void
	 *
	 * @deprecated 7.1
	 * @codeCoverageIgnore
	 */
	public function site_owner() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//As this is a frontend method, we want to make sure it is not displayed for non-logged in users.
		if ( function_exists( 'wp_get_current_user' ) && current_user_can( 'manage_options' ) ) {
			_deprecated_function( 'WPSEO_OpenGraph::site_owner', '7.1', null );
		}
	}

	/**
	 * Fallback method for plugins using image_output.
	 *
	 * @param string|bool $image Image URL.
	 *
	 * @deprecated 7.4
	 * @codeCoverageIgnore
	 */
	public function image_output( $image = false ) {
		_deprecated_function( 'WPSEO_OpenGraph::image_output', '7.4', 'WPSEO_OpenGraph::image' );

		$this->image( $image );
	}
} /* End of class */
