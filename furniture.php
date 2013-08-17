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

        add_action( 'init', array( &$this, 'register_type' ) );

        add_action( 'init', array( &$this, 'register_taxa' ) );

        add_action( 'save_post', array( &$this, 'price_save' ) );

    }

    public function init() {

        $this->width  = get_option( DOMAIN . '_width',  64 );
        $this->height = get_option( DOMAIN . '_height', 64 );

    }

    public function register_type() {

        $labels = array(
            'name'				=> _x( 'Furniture',  'register_type: name', DOMAIN ),
            'singular_name'		=> _x( 'Furniture',  'register_type: singular_name', DOMAIN ),
            'add_new'			=> _x( 'Add New',  'register_type: add_new', DOMAIN ),
            'add_new_item'		=> _x( 'Add New Product',  'register_type: add_new_item', DOMAIN ),
            'edit_item'			=> _x( 'Edit Product',  'register_type: edit_item', DOMAIN ),
            'new_item'			=> _x( 'New Product',  'register_type: new_item', DOMAIN ),
            'all_items'			=> _x( 'All Products',  'register_type: all_items', DOMAIN ),
            'view_item'			=> _x( 'View Product',  'register_type: view_item', DOMAIN ),
            'search_items'		=> _x( 'Search Products',  'register_type: search_items', DOMAIN ),
            'not_found'			=> _x( 'Product(s) Not Found',  'register_type: not_found', DOMAIN ),
            'not_found_in_trash'=> _x( 'Product(s) Not Found in Trash',  'register_type: not_found_in_trash', DOMAIN ),
            'parent_item_colon'	=> _x( '',  'register_type: parent_item_colon', DOMAIN ),
            'menu_name'			=> _x( 'Furniture',  'register_type: menu_name', DOMAIN )
        );

        register_post_type(
            DOMAIN,
            array(
                'labels' => $labels,
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

    public function register_taxa() {

        $manufacturer_labels = array(
            'name'              => _x( 'Manufacturers', 'register_taxonomy: name', DOMAIN ),
            'singular_name'     => _x( 'Manufacturer', 'register_taxonomy: singular_name', DOMAIN ),
            'search_items'      => _x( 'Search Manufacturers', 'register_taxonomy: search_items', DOMAIN ),
            'all_items'         => _x( 'All Manufacturers', 'register_taxonomy: all_items', DOMAIN ),
            'edit_item'         => _x( 'Edit Manufacturer', 'register_taxonomy: edit_item', DOMAIN ),
            'update_item'       => _x( 'Update Manufacturer', 'register_taxonomy: update_item', DOMAIN ),
            'add_new_item'      => _x( 'Add New Manufacturer', 'register_taxonomy: add_new_item', DOMAIN ),
            'new_item_name'     => _x( 'New Manufacturer Name', 'register_taxonomy: new_item_name', DOMAIN ),
            'menu_name'         => _x( 'Manufacturer', 'register_taxonomy: menu_name', DOMAIN ),
        );

        $manufacturer_args = array(
            'hierarchical'      => true,
            'labels'            => $manufacturer_labels,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'manufacturer' ),
        );

        register_taxonomy( 'manufacturer', 'furniture', $manufacturer_args );

    }

    public function add_price_box() {

        add_meta_box(
            DOMAIN . '_price_box',
            'Price',
            array( &$this, 'price_box' ),
            DOMAIN,
            'side',
            'default'
        );

    }


    public function price_box( $post ) {

        wp_nonce_field( plugin_basename( __FILE__ ), DOMAIN );

        $price = get_post_meta( $post->ID, '_' . DOMAIN . '_price', true );

        echo <<<HTML
Est'd. Price $
<input
    type  = "text"
    name  = "_{$this->domain}_price"
    id    = "_{$this->domain}_price"
    value = "{$price}"
    size  = "5"
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
