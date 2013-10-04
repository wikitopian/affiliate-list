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

		add_action( 'save_post', array( &$this, 'url_box_save' ) );

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
				'has_archive' => false,
				'supports' =>
				array(
					'title',
					'thumbnail',
				),
				'register_meta_box_cb' =>
				array( &$this, 'add_url_box' )
			)
		);

	}

	public function add_url_box() {

		add_meta_box(
			$this->prefix . '_url_box',
			'Testimonials',
			array( &$this, 'url_box' ),
			$this->prefix,
			'normal',
			'high'
		);

	}


	public function url_box( $post ) {

		wp_nonce_field( plugin_basename( __FILE__ ), $this->prefix );

		$url = get_post_meta( $post->ID, '_' . $this->prefix . '_url', true );

?>

<input
	type="text"
	name="_<?php echo sanitize_text_field( $this->prefix ); ?>_url"
	id  ="_<?php echo sanitize_text_field( $this->prefix ); ?>_url"
	value="<?php echo sanitize_text_field( $url ); ?>"
	size="100%"
	/>

<?php

	}

	public function url_box_save( $post_id ) {

		if (
			!isset( $_POST[$this->prefix] )
			||
			!wp_verify_nonce( $_POST[$this->prefix], plugin_basename( __FILE__ ) )
		) {
			return;
		}

		$url = $_POST['_' . $this->prefix . '_url'];
		$url = sanitize_text_field( $url );

		update_post_meta( $post_id, '_' . $this->prefix . '_url', $url );

	}

	public function show( $echo = true ) {

		$loop = new WP_Query(
			array(
				'post_type' => $this->prefix,
				'posts_per_page' => $this->max,
			)
		);

		$html = "<div id='{$this->prefix}'>\n<ul>";

		for ( $loop->have_posts() ) {
			$loop->the_post();
			global $post;

			$thumb = get_the_post_thumbnail(
				$post->ID,
				array( $this->width, $this->height )
			);
			$url   = get_post_meta( $post->ID, '_' . $this->prefix . '_url', true );


			$html .= <<<HTML

<li>
	<a href="{$url}">
		{$thumb}
	</a>
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
