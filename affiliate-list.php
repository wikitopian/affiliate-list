<?php
/*
Plugin Name: Affiliate List
Plugin URI: http://www.github.com/wikitopian/affiliate-list
Description: Easily add and remove affiliate thumbs
Version: 0.1.0
Author: Matt Parrott
Author URI: http://www.swarmstrategies.com/matt
License: GPLv2
 */

class Affiliate_List {

    private $prefix;

    public function __construct() {

        $this->prefix = 'affiliate_list';

        add_action( 'init', array( &$this, 'init' ) );

        add_action( 'save_post', array( &$this, 'url_box_save' ) );

    }

    public function init() {

        register_post_type(
            $this->prefix,
            array(
                'labels' =>
                array(
                    'name' => 'Affiliate List',
                    'singular_name' => 'Affiliate'
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 101,
                'has_archive' => false,
                'supports' =>
                array(
                    'title',
                    'thumbnail'
                ),
                'register_meta_box_cb' =>
                array( &$this, 'add_url_box' )
            )
        );

    }

    public function add_url_box() {

        add_meta_box(
            $this->prefix . '_url_box',
            'Affiliate Link',
            array( &$this, 'url_box' ),
            $this->prefix,
            'normal',
            'high'
        );

    }


    public function url_box( $post ) {

        wp_nonce_field( plugin_basename( __FILE__ ), $this->prefix );

        $url = get_post_meta( $post->ID, '_' . $this->prefix . '_url', true );

        echo <<<HTML

<input
    type="text"
    name="_{$this->prefix}_url"
    id  ="_{$this->prefix}_url"
    value="{$url}"
    size="100%"
    />

HTML;

    }

    public function url_box_save( $post_id ) {

        if(
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

}

$affiliate_list = new Affiliate_List();
