<?php
/**
 * shopminator functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package shopminator
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.6' );
}

if ( ! function_exists( 'shopminator_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function shopminator_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/**
		 * Egyedi menük használata
		 */
		function wp_custom_new_menu() {
			register_nav_menus(
				array(
					'menu-1' => esc_html__( 'Primary', 'shopminator' ),
					'menu-2' => esc_html__( 'Secondary', 'shopminator' ),
				)
			);
		}
		add_action( 'init', 'wp_custom_new_menu' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'shopminator_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'shopminator_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function shopminator_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'shopminator_content_width', 640 );
}
add_action( 'after_setup_theme', 'shopminator_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function shopminator_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'shopminator' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'shopminator' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'shopminator_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function shopminator_scripts() {
	wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css' );
	wp_enqueue_style( 'shopminator-main', get_template_directory_uri() . '/assets/css/main.css?v=' . _S_VERSION );

	wp_enqueue_script( 'shopminator-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'shopminator_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/**
 * Footer Widget One
 */

function footer_widget_one () {
	$args = array (
		'id'				=> 'footer-widget-col-one',
		'name'				=> __('Footer Column One', 'text-domain'),
		'description'		=> __('Column One', 'text-domain'),
		'before_title'		=> '<h5 class="title">',
		'after_title'		=> '</h5>',
		'before_widget'		=> '<div id="%1$s" class="widget %2$s">',
		'after_widget'		=> '</div>'
	);
	register_sidebar($args);
}
add_action('widgets_init', 'footer_widget_one');

function footer_widget_two () {
	$args = array (
		'id'				=> 'footer-widget-col-two',
		'name'				=> __('Footer Column Two', 'text-domain'),
		'description'		=> __('Column Two', 'text-domain'),
		'before_title'		=> '<h5 class="title">',
		'after_title'		=> '</h5>',
		'before_widget'		=> '<div id="%1$s" class="widget %2$s">',
		'after_widget'		=> '</div>'
	);
	register_sidebar($args);
}
add_action('widgets_init', 'footer_widget_two');


add_action( 'woocommerce_shortcode_before_products_loop', 'roka_before_products_shortcode_loop', 1, 10 );
add_action( 'woocommerce_shortcode_after_products_loop', 'roka_after_products_shortcode_loop', 0, 10 );

function roka_before_products_shortcode_loop( $atts ) {
    $GLOBALS[ 'roka_woocommerce_loop_template' ] =
        ( isset( $atts[ 'class' ] ) ? $atts[ 'class' ] : '' );
}

function roka_after_products_shortcode_loop( $atts ) {
    $GLOBALS[ 'roka_woocommerce_loop_template' ] = '';
}

/**
 * Visszaadja a temék kategóriákat.
 */
require_once get_template_directory() . '/classes/getProductCategories.php';

/**
 * Visszaadja egy terméket megjeleníthető formában
 */
require_once get_template_directory()."/classes/productCardForSlider.php";

/**
 * sql kódokat hozza be amelyeket majd megívhatunk
 */
//require_once get_template_directory()."/classes/queries.php";

/**
 * Ajax request
 */
require_once get_template_directory()."/classes/productAjaxLoad.php";

/**
 * Ajax product loader
 */
require_once get_template_directory()."/classes/ajaxProductLoaderQueries.php";

/**
 * Filter form generator
 */
require_once get_template_directory()."/classes/productOrdering.php";
