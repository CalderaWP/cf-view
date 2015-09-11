<?php
/**
 * FooTable Setup Class
 *
  Based on FooTable Plugin main class by Brad Vincent <brad@fooplugins.com> copyright 2013 FooPlugins LLC license GPL v2+
 */

namespace calderawp\view;





class FooTable extends Foo_Plugin_Base_v1_1 {

	const JS = 'footable.min.js';
	const JS_SORT = 'footable.sort.min.js';
	const JS_FILTER = 'footable.filter.min.js';
	const JS_PAGINATE = 'footable.paginate.min.js';

	const CSS = 'footable.core.min.css';
	const CSS_BOOTSTRAP = 'bootstrap.2.3.1.css';
	const CSS_METRO = 'footable.metro.min.css';
	const CSS_STANDALONE = 'footable.standalone.min.css';

	const URL_HOMEPAGE = 'http://fooplugins.com/plugins/footable-lite/';
	const URL_GITHUB = 'https://github.com/bradvin/FooTable';
	const URL_JQUERY = 'http://fooplugins.com/plugins/footable-jquery/';
	const URL_JQUERY_DEMOS = 'http://fooplugins.com/footable-demos/';
	const URL_DOCS = 'http://fooplugins.com/footable-lite/documentation/';

	function __construct($file) {
		$this->init(  $file, 'footable', '0.3.1', 'FooTable' );
		$this->init_footable();
	}

	function init_footable() {
		if ( ! is_admin()) {
			$this->frontend_init();
		}
	}



	function generate_javascript($debug = false) {
		$js = '/* FooTable init code */
';
		$no_js = true;

		$js .= '
var $FOOTABLE = $FOOTABLE || {};
(function( $FOOTABLE, $, undefined ) {

jQuery.fn.attrAppendWithComma=function(a,b){var c;return this.each(function(){c=$(this),void 0!==c.attr(a)&&""!=c.attr(a)?c.attr(a,c.attr(a)+","+b):c.attr(a,b)})};jQuery.fn.footableAttr=function(a,b){return this.each(function(){var c=$(this);c.data("auto-columns")!==!1&&(c.find("thead th:gt("+a+")").attrAppendWithComma("data-hide","tablet"),c.find("thead th:gt("+b+")").attrAppendWithComma("data-hide","phone"))})},jQuery.fn.footableFilter=function(a){return this.each(function(){var b=$(this);b.data("filter")||b.data("filter")===!1||b.data("filter-text-only","true").before(\'<div class="footable-filter-container"><input placeholder="\'+a+\'" style="float:right" type="text" class="footable-filter" /></div>\')})},jQuery.fn.footablePager=function(){return this.each(function(){var a=$(this);if(a.data("page")!==!1){var b=$(\'<tfoot class="hide-if-no-paging"><tr><td><div class="pagination pagination-centered"></div></td></tr></tfoot>\');b.find("td").attr("colspan",a.find("thead th").length),a.find("tbody:last").after(b)}})};

$FOOTABLE.init = function() {
';
		//@todo use options
		/**
		$breakpoint_tablet = $this->options()->get_int( 'breakpoint_tablet', 768 );
		$breakpoint_phone = $this->options()->get_int( 'breakpoint_phone', 320 );

		$columns_tablet = $this->options()->get_int( 'columns_tablet', 4 ) - 1;
		$columns_phone = $this->options()->get_int( 'columns_phone', 2 ) - 1;
		$manual_columns = $this->options()->is_checked( 'manual_columns' );
		$filtering = $this->options()->is_checked('enable_filtering', true);
		$pagination = $this->options()->is_checked('enable_pagination', true);
		$tablepress = $this->options()->is_checked('tablepress', true);

**/
		$breakpoint_tablet =  768;
		$breakpoint_phone = 320;

		$columns_tablet = 3;
		$columns_phone = 1;
		$manual_columns = false;
		$filtering = true;
		$pagination = true;
		$tablepress = true;

		//get custom JS (Before) from the settings page
		$custom_js_before = '';

		if ( !empty($custom_js_before) ) {
			$no_js = false;
			$js .= '    ' . $custom_js_before . '
';
		}

		/**
		$selector = $this->options()->get( 'selector', '.footable' );
		if ($tablepress) {
			$selector .= ', .tablepress';
		}
		if ( $this->screen()->is_plugin_settings_page() ) {
			$selector .= ', .footable-demo';
		}
		 */
		$selector = apply_filters( 'cf_view_table_class', 'cf-view-table' );

		if ( !empty( $selector ) ) {
			$no_js = false;
			$js .= '		$("'.$selector.'")
';

			if ( !$manual_columns ) {
				$js .= '			.footableAttr('.$columns_tablet.','.$columns_phone.')
';
			}
			if ($filtering) {
				$js .= '			.footableFilter("' . __('search','footable') . '")
';
			}
			if ($pagination) {
				$js .= '			.footablePager()
';
			}

			$js .= '			.footable( { breakpoints: { phone: '.$breakpoint_phone.', tablet: '.$breakpoint_tablet.' } });
';
		}

		//get custom JS from the settings page
		//$custom_js_after = $this->options()->get( 'custom_js_after' );
		$custom_js_after = false;
		if ( !empty($custom_js_after) ) {
			$no_js = false;
			$js .= '    ' . $custom_js_after . '
';
		}

		$js .= '
};
}( $FOOTABLE, jQuery ));

jQuery(function($) {
$FOOTABLE.init();
});
';

		if ($no_js) { return ''; }

		return $js;
	}

	function render_debug_info() {
		echo '<strong>Javascript:<br /><pre>';
		echo htmlentities($this->generate_javascript(true));
		echo '</pre><br />Settings:<br /><pre>';
		print_r( get_option( $this->plugin_slug ) );
		echo '</pre>';
	}



	function frontend_init() {
		add_action( 'wp_enqueue_scripts', array($this, 'add_styles'), 12 );
		add_action( 'wp_enqueue_scripts', array($this, 'add_scripts'), 12 );


		$where = 'wp_print_footer_scripts';

		//add_action($where, array($this, 'inline_dynamic_js') );

		//add_action('wp_print_styles', array($this, 'inline_dynamic_css') );
	}

	function add_styles() {
		if (is_admin() && $this->screen()->is_plugin_settings_page()) {
			$this->register_and_enqueue_css(self::CSS_BOOTSTRAP);
		}

		//enqueue footable CSS
		$this->register_and_enqueue_css(self::CSS);

		$theme = $this->options()->get('theme', 'bootstrap');
		if ($theme === 'metro') {
			$this->register_and_enqueue_css(self::CSS_METRO);
		} else if ($theme === 'original') {
			$this->register_and_enqueue_css(self::CSS_STANDALONE);
		}
	}

	function add_scripts() {
		//put JS in footer?
		$infooter = true;

		//enqueue core JS
		$this->register_and_enqueue_js(self::JS, array('jquery'), false, $infooter);

		//enqueue sorting
		if ($this->options()->is_checked('enable_sorting', true)) {
			$this->register_and_enqueue_js(self::JS_SORT, array('jquery'), false, $infooter);
		}

		//enqueue filtering
		if ($this->options()->is_checked('enable_filtering', true)) {
			$this->register_and_enqueue_js(self::JS_FILTER, array('jquery'), false, $infooter);
		}

		//enqueue paging
		if ($this->options()->is_checked('enable_pagination', true)) {
			$this->register_and_enqueue_js(self::JS_PAGINATE, array('jquery'), false, $infooter);
		}
	}


	function inline_dynamic_js() {
		$footable_js = $this->generate_javascript();

		echo '<script type="text/javascript">' . $footable_js . '</script>';
	}

	function inline_dynamic_css() {

		//get custom CSS from the settings page
		$custom_css = $this->options()->get( 'custom_css', '' );

		if (class_exists('TablePress')) {
			$custom_css .= '.tablepress thead th div { float:left; }';
		}

		if (empty($custom_css)) return;

		echo '<style type="text/css">
' . $custom_css;
		echo '
</style>';
	}
}

