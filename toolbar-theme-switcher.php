<?php
/*
Plugin Name: Toolbar Theme Switcher
Plugin URI: https://github.com/Rarst/toolbar-theme-switcher
Description: Adds toolbar menu that allows users to switch theme for themselves.
Author: Andrey "Rarst" Savchenko
Version: 1.4
Author URI: http://www.rarst.net/
Text Domain: toolbar-theme-switcher
Domain Path: /lang
License: MIT
*/

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

Toolbar_Theme_Switcher::on_load();
