<?php

/*
Plugin Name: Webp
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Converts png/jpg images into webp on fly, if browser supports webp.
Version: 1.0
Author: Vasyl Dmytruk
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

use webp\classes\WebpGenerator;
use webp\classes\WebpReplacer;

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once( __DIR__ . '/vendor/autoload.php' );
}

spl_autoload_register( static function ( $class ) {
	$dir       = __DIR__;
	$root      = str_replace( '/' . basename( $dir ), '', $dir );
	$classFile = $root . '/' . str_replace( '\\', '/', $class ) . '.php';

	if ( ! is_file( $classFile ) ) {
		return;
	}

	include $classFile;
} );

$webpReplacer  = WebpReplacer::getInstance();
$webpGenerator = WebpGenerator::getInstance();

add_filter( 'the_content', [ $webpReplacer, 'replace' ] );
add_action( 'template_redirect', [ $webpGenerator, 'generateIfNeed' ] );
