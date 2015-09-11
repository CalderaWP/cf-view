<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



define('CF_VIEW_FOOTABLE_PATH', dirname( __FILE__ ) );

// Includes
require_once( CF_VIEW_FOOTABLE_PATH . '/includes/Foo_Plugin_Base.php' );
require_once( 'class.footable.php' );
