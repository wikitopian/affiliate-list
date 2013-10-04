<?php
/*
Plugin Name: Testimonials
Plugin URI: http://www.github.com/wikitopian/affiliate-list
Description: Easily add and remove client testimonials
Version: 0.1.0
Author: @wikitopian
Author URI: http://www.github.com/wikitopian
License: GPLv2
 */

class Testimonials {

	private $prefix;
	private $max;

	private $width;
	private $height;

	public function __construct() {

		$this->prefix = 'testimonials';
		$this->max    = 8;

		add_action( 'init', array( &$this, 'init' ) );

		add_action( 'save_post', array( &$this, 'testimonial_box_save' ) );

	}

	public function init() {

		$this->width  = get_option( $this->prefix . '_width',  64 );
		$this->height = get_option( $this->prefix . '_height', 64 );

		register_post_type(
			$this->prefix,
			array(
				'labels' =>
				array(
					'name' => 'Testimonials',
					'singular_name' => 'Testimonial',
				),
				'public' => true,
				'has_archive' => true,
				'show_in_nav_menus' => false,
				'menu_position' => 101,
				'supports' =>
				array(
					'title',
					'thumbnail',
				),
				'register_meta_box_cb' =>
				array( &$this, 'add_testimonial_box' )
			)
		);

	}

	public function add_testimonial_box() {

		add_meta_box(
			$this->prefix . '_testimonial_box',
			'Testimonial',
			array( &$this, 'testimonial_box' ),
			$this->prefix,
			'normal',
			'high'
		);

	}


	public function testimonial_box( $post ) {

		wp_nonce_field( plugin_basename( __FILE__ ), $this->prefix );

		$testimonial = get_post_meta( $post->ID, '_' . $this->prefix . '_testimonial', true );

		wp_editor( $testimonial, $this->prefix . '_testimonial' );

	}

	public function testimonial_box_save( $post_id ) {

		if (
			!isset( $_POST[$this->prefix] )
			||
			!wp_verify_nonce( $_POST[$this->prefix], plugin_basename( __FILE__ ) )
		) {
			return;
		}

		$testimonial = $_POST['_' . $this->prefix . '_testimonial'];
		$testimonial = sanitize_text_field( $testimonial );

		update_post_meta( $post_id, '_' . $this->prefix . '_testimonial', $testimonial );

	}

	public function show( $echo = true ) {

		$loop = new WP_Query(
			array(
				'post_type' => $this->prefix,
				'posts_per_page' => $this->max,
			)
		);

		$html = "<div id='{$this->prefix}'>\n<ul>";

		while ( $loop->have_posts() ) {
			$loop->the_post();
			global $post;

			$thumb = get_the_post_thumbnail(
				$post->ID,
				array( $this->width, $this->height )
			);
			$testimonial = get_post_meta( $post->ID, '_' . $this->prefix . '_testimonial', true );


			$html .= <<<HTML

<li>
		{$thumb}
		<p>{$testimonial}</p>
</li>

HTML;
		}

		$html .= "\n</ul>\n</div>\n";

		if ( $echo ) {
			echo sanitize_text_field( $html );
		} else {
			return $html;
		}

	}

}

$testimonials = new Testimonials();
