<?php
/*
Plugin Name: Toolbar Theme Switcher
Plugin URI: http://wordpress.org/extend/plugins/toolbar-theme-switcher/
Description: Adds toolbar menu that allows users to switch theme for themselves.
Author: Andrey "Rarst" Savchenko
Version: 1.1.4
Author URI: http://www.rarst.net/
License: MIT

Copyright (c) 2012 Andrey Savchenko

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
*/

Toolbar_Theme_Switcher::on_load();

/**
 * Main plugin class.
 */
class Toolbar_Theme_Switcher {

	/**
	 * Gets set to non-current theme name if defined in cookie.
	 *
	 * @var string|boolean
	 */
	static $theme_name = false;

	/**
	 * Hooks that need to be set up early.
	 */
	static function on_load() {

		add_action( 'setup_theme', array( __CLASS__, 'setup_theme' ) );
		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Loads cookie and sets up theme filters.
	 */
	static function setup_theme() {

		if ( self::can_switch_themes() ) {
			self::load_cookie();

			if ( self::$theme_name ) {
				self::get_allowed_themes(); // cache early, filters break it with multiple theme dirs
				add_filter( 'template', array( __CLASS__, 'template' ) );
				add_filter( 'stylesheet', array( __CLASS__, 'stylesheet' ) );
			}
		}
	}

	/**
	 * If allowed to switch theme.
	 *
	 * @return boolean
	 */
	static function can_switch_themes() {

		$capability = apply_filters( 'tts_capability', 'switch_themes' );

		return apply_filters( 'tts_can_switch_themes', current_user_can( $capability ) );
	}

	/**
	 * Sets if cookie is defined to non-default theme.
	 */
	static function load_cookie() {

		$cookie_name = self::get_cookie_name();

		if ( ! empty( $_COOKIE[$cookie_name] ) ) {
			if ( $_COOKIE[$cookie_name] != get_option( 'current_theme' ) )
				self::$theme_name = $_COOKIE[$cookie_name];
		}
	}

	/**
	 * Returns cookie name, based on home URL so it differs for sites in multisite.
	 *
	 * @return string
	 */
	static function get_cookie_name() {

		static $hash;

		if ( empty($hash) )
			$hash = 'wordpress_tts_theme_' . md5( home_url( '', 'http' ) );

		return $hash;
	}

	/**
	 * Template slug filter.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	static function template( $template ) {

		return self::get_theme_field( 'Template', $template );
	}

	/**
	 * Stylesheet slug filter.
	 *
	 * @param string $stylesheet
	 *
	 * @return string
	 */
	static function stylesheet( $stylesheet ) {

		return self::get_theme_field( 'Stylesheet', $stylesheet );
	}

	/**
	 * Returns field from theme data if cookie is set to valid theme.
	 *
	 * @param string $field_name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	static function get_theme_field( $field_name, $default = false ) {

		if ( ! empty( self::$theme_name ) ) {
			$themes = self::get_allowed_themes();

			if ( isset( $themes[self::$theme_name][$field_name] ) )
				return $themes[self::$theme_name][$field_name];
		}

		return $default;
	}

	/**
	 * Retrieves allowed themes, needs to load some admin files when on front-end.
	 *
	 * @return array
	 */
	static function get_allowed_themes() {

		static $themes;

		if ( empty( $themes ) ) {
			require_once ABSPATH . '/wp-admin/includes/theme.php';

			if ( is_multisite() )
				require_once ABSPATH . '/wp-admin/includes/ms.php';

			if ( function_exists( 'wp_get_themes' ) )  {
				$wp_themes = wp_get_themes( array( 'allowed' => true ) );
				$themes = array();

				/**
				 * @var WP_Theme $theme
				 */
				foreach ( $wp_themes as $theme ) {
					$themes[$theme->get( 'Name' )] = $theme;
				}
			}
			elseif ( function_exists( 'get_allowed_themes' ) ) {
				$themes = get_allowed_themes();
			}
			else {
				$themes = array();
			}

			$themes = apply_filters( 'tts_allowed_themes', $themes );
		}

		return $themes;
	}

	/**
	 * Sets up hooks that doesn't need to happen early.
	 */
	static function init() {

		if ( self::can_switch_themes() ) {
			add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_menu' ), 90 );
			add_action( 'wp_ajax_tts_set_theme', array( __CLASS__, 'set_theme' ) );
		}
	}

	/**
	 * Creates menu in toolbar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	static function admin_bar_menu( $wp_admin_bar ) {

		$themes  = self::get_allowed_themes();
		$current = ( ! empty( self::$theme_name ) ) ? self::$theme_name : get_option( 'current_theme' );
		$title   = apply_filters( 'tts_root_title', 'Theme: ' . $current );

		$wp_admin_bar->add_menu( array(
			'id'		=> 'toolbar_theme_switcher',
			'title' => $title,
		) );

		foreach ( $themes as $theme ) {
			$wp_admin_bar->add_menu( array(
				'id'     => $theme['Stylesheet'],
				'title'  => $theme['Name'],
				'href'   => $current == $theme['Name'] ? null : add_query_arg( array( 'action' => 'tts_set_theme', 'theme' => urlencode( $theme['Name'] ) ), admin_url( 'admin-ajax.php' ) ),
				'parent' => 'toolbar_theme_switcher',
			) );
		}
	}

	/**
	 * Saves selected theme in cookie if valid.
	 */
	static function set_theme() {

		$theme_name = $_REQUEST['theme'];

		if ( array_key_exists( $theme_name, self::get_allowed_themes() ) )
			setcookie( self::get_cookie_name(), $theme_name, strtotime( '+1 year' ), COOKIEPATH );

		wp_safe_redirect( wp_get_referer() );
		die;
	}
}