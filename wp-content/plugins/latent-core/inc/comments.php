<?php
/**
 * Disable comments and pings site-wide.
 *
 * @package LatentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );
