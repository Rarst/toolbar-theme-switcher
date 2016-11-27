<?php

/**
 * Main plugin class.
 */
class Toolbar_Theme_Switcher {

	/** @var WP_Theme $theme */
	public static $theme = false;

	/**
	 * Hooks that need to be set up early.
	 */
	public static function on_load() {

		add_action( 'setup_theme', array( __CLASS__, 'setup_theme' ) );
		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Loads cookie and sets up theme filters.
	 */
	public static function setup_theme() {

		global $pagenow;

		if ( ( is_admin() && 'themes.php' == $pagenow ) || ! self::can_switch_themes() ) {
			return;
		}

		self::check_reset();
		self::load_cookie();

		if ( empty( self::$theme ) ) {
			return;
		}

		add_filter( 'pre_option_template', array( self::$theme, 'get_template' ) );
		add_filter( 'pre_option_stylesheet', array( self::$theme, 'get_stylesheet' ) );
		add_filter( 'pre_option_stylesheet_root', array( self::$theme, 'get_theme_root' ) );
		$parent = self::$theme->parent();
		add_filter( 'pre_option_template_root', array( empty( $parent ) ? self::$theme : $parent, 'get_theme_root' ) );
		add_filter( 'pre_option_current_theme', '__return_false' );
	}

	/**
	 * Clear theme choice if reset variable is present in request.
	 */
	public static function check_reset() {

		if ( ! empty( filter_input( INPUT_GET, 'tts_reset' ) ) ) {
			setcookie( self::get_cookie_name(), '', 1 );
			nocache_headers();
			wp_safe_redirect( home_url() );
			die;
		}
	}

	/**
	 * If allowed to switch theme.
	 *
	 * @return boolean
	 */
	public static function can_switch_themes() {

		$capability = apply_filters( 'tts_capability', 'switch_themes' );

		return apply_filters( 'tts_can_switch_themes', current_user_can( $capability ) );
	}

	/**
	 * Sets if cookie is defined to non-default theme.
	 */
	public static function load_cookie() {

		$theme_name  = filter_input( INPUT_COOKIE, self::get_cookie_name() );

		if ( ! $theme_name ) {
			return;
		}

		$theme = wp_get_theme( $theme_name );

		if (
			$theme->exists()
			&& $theme->get( 'Name' ) !== get_option( 'current_theme' )
			&& $theme->is_allowed()
		) {
			self::$theme = $theme;
		}
	}

	/**
	 * Returns cookie name, based on home URL so it differs for sites in multisite.
	 *
	 * @return string
	 */
	public static function get_cookie_name() {

		static $hash;

		if ( empty( $hash ) ) {
			$hash = 'wordpress_tts_theme_' . md5( home_url( '', 'http' ) );
		}

		return $hash;
	}

	/**
	 * Retrieves allowed themes.
	 *
	 * @return WP_Theme[]
	 */
	public static function get_allowed_themes() {

		static $themes;

		if ( isset( $themes ) ) {
			return $themes;
		}

		$wp_themes = wp_get_themes( array( 'allowed' => true ) );

		/** @var WP_Theme $theme */
		foreach ( $wp_themes as $theme ) {

			// Make keys names (rather than slugs) for backwards compat.
			$themes[ $theme->get( 'Name' ) ] = $theme;
		}

		$themes = apply_filters( 'tts_allowed_themes', $themes );

		return $themes;
	}

	/**
	 * Sets up hooks that doesn't need to happen early.
	 */
	public static function init() {

		if ( self::can_switch_themes() ) {
			add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_menu' ), 90 );
			add_action( 'wp_ajax_tts_set_theme', array( __CLASS__, 'set_theme' ) );
		}

		load_plugin_textdomain( 'toolbar-theme-switcher', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/lang' );
	}

	/**
	 * Creates menu in toolbar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		$themes  = self::get_allowed_themes();
		$current = empty( self::$theme ) ? wp_get_theme() : self::$theme;
		unset( $themes[ $current->get( 'Name' ) ] );
		uksort( $themes, array( __CLASS__, 'sort_core_themes' ) );

		$title = apply_filters( 'tts_root_title', sprintf( __( 'Theme: %s', 'toolbar-theme-switcher' ), $current->display( 'Name' ) ) );

		$wp_admin_bar->add_menu( array(
			'id'    => 'toolbar_theme_switcher',
			'title' => $title,
			'href'  => admin_url( 'themes.php' ),
		) );

		$ajax_url = admin_url( 'admin-ajax.php' );

		foreach ( $themes as $theme ) {

			$href = add_query_arg( array(
				'action' => 'tts_set_theme',
				'theme'  => urlencode( $theme->get_stylesheet() ),
			), $ajax_url );

			$wp_admin_bar->add_menu( array(
				'id'     => $theme['Stylesheet'],
				'title'  => $theme->display( 'Name' ),
				'href'   => $href,
				'parent' => 'toolbar_theme_switcher',
			) );
		}
	}

	/**
	 * Callback to sort theme array with core themes in numerical order by year.
	 *
	 * @param string $theme_a First theme name.
	 * @param string $theme_b Second theme name.
	 *
	 * @return int
	 */
	public static function sort_core_themes( $theme_a, $theme_b ) {

		static $twenties = array(
			'Twenty Ten',
			'Twenty Eleven',
			'Twenty Twelve',
			'Twenty Thirteen',
			'Twenty Fourteen',
			'Twenty Fifteen',
			'Twenty Sixteen',
			'Twenty Seventeen',
			'Twenty Eighteen',
			'Twenty Nineteen',
			'Twenty Twenty',
		);

		if ( 0 === strpos( $theme_a, 'Twenty' ) && 0 === strpos( $theme_b, 'Twenty' ) ) {

			$index_a = array_search( $theme_a, $twenties, true );
			$index_b = array_search( $theme_b, $twenties, true );

			if ( false !== $index_a && false !== $index_b ) {
				return ( $index_a < $index_b ) ? - 1 : 1;
			}
		}

		return strcasecmp( $theme_a, $theme_b );
	}

	/**
	 * Saves selected theme in cookie if valid.
	 */
	public static function set_theme() {

		$stylesheet = filter_input( INPUT_GET, 'theme' );
		$theme      = wp_get_theme( $stylesheet );

		if ( $theme->exists() && $theme->is_allowed() ) {
			setcookie( self::get_cookie_name(), $theme->get_stylesheet(), strtotime( '+1 year' ), COOKIEPATH );
		}

		wp_safe_redirect( wp_get_referer() );
		die;
	}

	// <editor-fold desc="Deprecated">
	/**
	 * If theme is in list of allowed to be switched to.
	 *
	 * @deprecated :2.0
	 *
	 * @param WP_Theme $theme
	 *
	 * @return bool
	 */
	public static function is_allowed( $theme ) {

		return array_key_exists( $theme->get( 'Name' ), self::get_allowed_themes() );
	}

	/**
	 * Template slug filter.
	 *
	 * @param string $template
	 *
	 * @deprecated :2.0
	 *
	 * @return string
	 */
	public static function template( $template ) {

		return self::get_theme_field( 'Template', $template );
	}

	/**
	 * Stylesheet slug filter.
	 *
	 * @param string $stylesheet
	 *
	 * @deprecated :2.0
	 *
	 * @return string
	 */
	public static function stylesheet( $stylesheet ) {

		return self::get_theme_field( 'Stylesheet', $stylesheet );
	}

	/**
	 * Returns field from theme data if cookie is set to valid theme.
	 *
	 * @param string $field_name
	 * @param mixed  $default
	 *
	 * @deprecated :2.0
	 *
	 * @return mixed
	 */
	public static function get_theme_field( $field_name, $default = false ) {

		if ( ! empty( self::$theme ) ) {
			return self::$theme->get( $field_name );
		}

		return $default;
	}
	// </editor-fold>
}
