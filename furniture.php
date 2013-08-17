<?php
/*
Plugin Name: Office Wizard
Plugin URI: http://www.github.com/wikitopian/affiliate-list
Description: Empower customers to assemble their own fantasy office.
Version: 0.1.0
Author: Matt Parrott
Author URI: http://www.swarmstrategies.com/matt
License: GPLv2
 */

define( DOMAIN, 'furniture' );

class Furniture {

    private $domain;

    private $max;

    private $width;
    private $height;

    public function __construct() {

        $this->domain = constant( 'DOMAIN' );

        $this->max = 24;

        add_action( 'init', array( &$this, 'init' ) );

        add_action( 'save_post', array( &$this, 'price_save' ) );

    }

    public function init() {

        $this->width  = get_option( DOMAIN . '_width',  64 );
        $this->height = get_option( DOMAIN . '_height', 64 );

        register_post_type(
            DOMAIN,
            array(
                'labels' =>
                array(
                    'name' => 'Furniture',
                    'singular_name' => 'Furniture'
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 101,
                'has_archive' => false,
                'supports' =>
                array(
                    'editor',
                    'excerpt',
                    'revisions',
                    'title',
                    'thumbnail'
                ),
                'register_meta_box_cb' =>
                array( &$this, 'add_price_box' )
            )
        );

    }

    public function add_price_box() {

        add_meta_box(
            DOMAIN . '_price_box',
            'Price',
            array( &$this, 'price_box' ),
            DOMAIN,
            'normal',
            'high'
        );

    }


    public function price_box( $post ) {

        wp_nonce_field( plugin_basename( __FILE__ ), DOMAIN );

        $price = get_post_meta( $post->ID, '_' . DOMAIN . '_price', true );

        echo <<<HTML

<input
    type="text"
    name="_{$this->domain}_price"
    id  ="_{$this->domain}_price"
    value="{$price}"
    size="100%"
    />

HTML;

    }

    public function price_box_save( $post_id ) {

        if(
            !isset( $_POST[DOMAIN] )
            ||
            !wp_verify_nonce( $_POST[DOMAIN], plugin_basename( __FILE__ ) )
        ) {
            return;
        }

        $price = $_POST['_' . DOMAIN . '_price'];
        $price = sanitize_text_field( $price );

        update_post_meta( $post_id, '_' . DOMAIN . '_price', $price );

    }

    public function show( $echo = true ) {

        $loop = new WP_Query(
            array(
                'post_type' => DOMAIN,
                'posts_per_page' => $this->max
            )
        );

        $html = "<div id='{$this->domain}'>\n<ul>";

        while( $loop->have_posts() ) {
            $loop->the_post();
            global $post;

            $thumb = get_the_post_thumbnail(
                $post->ID,
                array( $this->width, $this->height )
            );
            $price   = get_post_meta( $post->ID, '_' . DOMAIN . '_price', true );


            $html .= <<<HTML

<li>
    <a href="{$price}">
        {$thumb}
    </a>
</li>

HTML;

        }

        $html .= "\n</ul>\n</div>\n";

        if( $echo ) {
            echo $html;
        } else {
            return $html;
        }

    }

}

$furniture = new Furniture();
