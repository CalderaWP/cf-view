<?php
/**
 * @package   CF View
 * @author    Josh Pollock for CalderaWP LLC <Josh@CalderaWP.com>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock for CalderaWP LLC <Josh@CalderaWP.com>
 *
 * @wordpress-plugin
 * Plugin Name: CF View
 * Plugin URI: http://calderawp.com
 * Description: Front-end viewer for Caldera Forms
 * Version:     0.0.4
 * Author:      Josh Pollock for CalderaWP LLC
 * Author URI:  http://calderawp.com
 * Text Domain: cf-view
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */
if ( ! defined( 'CFCORE_PATH') ) {
	return;
}

define( 'CF_VIEW_VER', '0.0.4' );

/**
 * Load the JS.
 *
 * @since 0.0.1
 */
add_action( 'wp_enqueue_scripts', function() {

	$footable_ver = '3.0.1';

	$min = '.min';
	if ( WP_DEBUG ) {
		$min = '';
	}

	$foos = cf_view_componets();
	foreach( $foos as $foo ) {
		wp_register_script( 'footable-' .$foo , plugin_dir_url( __FILE__ ) . "/assets/js/foo/footable.{$foo}{$min}.js", array( 'footable-core-3'), $footable_ver, true );
		wp_register_style( 'footable-' .$foo, plugin_dir_url( __FILE__ ) . "/assets/css/foo/footable.{$foo}{$min}.css", false, $footable_ver, false );
	}

	wp_register_script( 'footable-core-3', plugin_dir_url( __FILE__ ) . "/assets/js/foo/footable{$min}.js", false, $footable_ver, true );
	wp_register_style( 'footable-core-3', plugin_dir_url( __FILE__ ) . "/assets/css/foo/footable.standalone{$min}.css", false, $footable_ver, false );

	wp_register_script( 'cf-view', plugin_dir_url( __FILE__ ) .'/assets/js/cf-view.js', array( 'jquery' ), CF_VIEW_VER, true );

});

function cf_view_componets() {
	$foos = array(
		'filtering',
		'paging',
		'sorting'
	);

	return apply_filters( 'cf_view_foo_table_components', $foos );
}


/**
 * Create CF View interface.
 *
 * @since 0.0.3
 *
 * @param int $form_id ID of form to view
 * @param array $fields Optional. An array of fields to show. If empty, the default, all fields of form are shown.
 * @param null|int $editor_id Optional. ID of a page with the form on it, used for editing. If null, the default, no edit links are shown.
 *
 * @return string|void
 */
function cf_view( $form_id, $fields = array(), $editor_id = null  ) {
	return cf_view_three( $form_id, $fields, $editor_id );

}
/**
 * Create CF View interface.
 *
 * @since 0.0.1
 *
 * @param int $form_id ID of form to view
 * @param array $fields Optional. An array of fields to show. If empty, the default, all fields of form are shown.
 * @param null|int $editor_id Optional. ID of a page with the form on it, used for editing. If null, the default, no edit links are shown.
 *
 * @return string|void
 */
function cf_view_three( $form_id, $fields = array(), $editor_id = null  ) {
	if ( ! is_array( Caldera_Forms::get_form( $form_id ) ) ) {
		return;
	}

	require_once( CFCORE_PATH . 'classes/admin.php' );

	$data = Caldera_Forms_Admin::get_entries( $form_id );
	if( is_array( $data ) && isset( $data[ 'entries' ] ) && ! empty( $data ) ) {
		$entries = $data[ 'entries' ];
		if ( empty( $fields ) ) {
			$fields = $data['fields'];
		}

		$_fields = $fields;
		$fields = array();
		$form = Caldera_Forms::get_form( $form_id );

		$index = array_merge( wp_list_pluck( $form[ 'fields' ], 'ID' ),  wp_list_pluck( $form[ 'fields' ], 'slug' )  );

		//Josh - this array flip seems silly, but doing the array_merge the other way didn't work, trust me -Josh
		$index = array_flip( $index );
		foreach( $_fields as $slug => $label ) {
			if ( isset( $index[ $slug ])  ) {
				$id = $index[ $slug ];
				$fields[] = array(
					'label' => $label,
					'slug' => $slug,
					'ID' => $id
				);
			}
		}



		include_once dirname( __FILE__ ) . '/classes/table_three.php';

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'footable-core-3' );
		wp_enqueue_style( 'footable-core-3' );
		$foos = cf_view_componets();
		foreach( $foos as $foo ) {
			wp_enqueue_script( 'footable-' .$foo );
			wp_enqueue_style( 'footable-' .$foo );
		}



		wp_enqueue_script( 'cf-view' );
		$class = new \calderawp\view\table_three( $fields, $entries, $form_id, $editor_id );
		$js =  $class->get_js_config();
		wp_localize_script( 'cf-view', 'CF_VIEW_FOO_TABLE_OPTIONS', $js  );

		return $class->get_html();

	}

}




/**
 * Shortcode callback for cf_view callback
 *
 * @since 0.0.2
 *
 * @param array $atts
 *
 * @return string|void
 */
function cf_view_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id' => null,
			'fields' => array(),
			'editor_id' => 0
		),
		$atts, 'cf_view'
	);

	if ( is_null( $atts[ 'id' ] ) ) {
		return;
	}

	$maybe_form = Caldera_Forms::get_form( $atts[ 'id' ] );
	if ( is_array( $maybe_form ) ) {
		$form_id = $atts[ 'id' ];
	}else{
		return;

	}

	if ( ! empty( $atts[ 'fields' ] ) ) {
		$fields = explode( ',', $atts[ 'fields' ] );
	}else{
		$fields = $atts[ 'fields' ];
	}

	if ( 0 < absint( $atts[ 'editor_id' ] ) ) {
		$maybe_post = get_post( $atts[ 'editor_id' ] );
		if ( ! is_object( $maybe_post ) ) {
			$editor_id = null;
		}else{
			$editor_id = $atts[ 'editor_id' ];
		}
	}else{
		$editor_id = null;
	}

	return cf_view( $form_id, $fields, $editor_id );
}

/**
 * Add cf_view shortcode
 *
 * @since 0.0.2
 */
add_shortcode( 'cf_view', 'cf_view_shortcode' );
