<?php

namespace GM;

/*
  Plugin Name: GM Captcha
  Plugin URI: https://github.com/Giuseppe-Mazzapica/GMCaptcha
  Description: A simple captcha (and honeypot) implementation for WordPress.
  Version: 1.0.0
  Author: Giuseppe Mazzapica
  Author URI: http://gm.zoomlab.it
  License: GPLv2
 */

/*
  Copyright (C) 2014 Giuseppe Mazzapica

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/*
  The font Broken Glass by is created by JLH Fonts and released in public domain.
  see http://jlhfonts.blogspot.it/p/terms-of-use.html
 */

/*
  The Pimple (http://pimple.sensiolabs.org) by Fabien Potencier (fabien@symfony.com)
  is released under MIT license.
 */

if ( is_file( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Setup a Pimple container for the plugin
 *
 * @return Pimple plugin container
 */
function container() {
    static $container = NULL;
    if ( is_null( $container ) ) {
        $gd = function($c) {
            $img = ! isset( $c[ 'image' ] ) ? imagecreatefromjpeg( $c[ 'img' ] ) : $c[ 'image' ];
            return new ImageGD( $img, $c[ 'size' ], $c[ 'tools' ], $c[ 'options' ] );
        };
        $imagik = function($c) {
            $img = ! isset( $c[ 'image' ] ) ? new \Imagick( $c[ 'img' ] ) : $c[ 'image' ];
            return new ImageImagick( $img, $c[ 'size' ], $c[ 'options' ] );
        };
        $url = plugins_url( '/', __FILE__ );
        $options = [
            'lines'     => 10,
            'dots'      => 100,
            'color'     => '7d1ac5',
            'font'      => plugin_dir_path( __FILE__ ) . 'BrokenGlass.ttf',
            'chars_num' => 4
        ];
        $c = [
            'url'     => $url,
            'tools'   => new Tools,
            'gd'      => $gd,
            'imagick' => $imagik,
            'img'     => apply_filters( 'gmcaptcha_base_img', $url . '/noise.jpg' ),
            'size'    => apply_filters( 'gmcaptcha_default_size', [ 140, 70 ] ),
            'options' => apply_filters( 'gmcaptcha_defaults', $options )
        ];
        $orig = new \Pimple( $c );
        $filtered = apply_filters( 'gmcaptcha_container', $orig );
        $container = $filtered instanceof \Pimple ? $filtered : $orig;
    }
    return $container;
}

/**
 * Print the the captcha fields
 *
 * @param array $args customization arguments
 */
function captcha( $args = [ ] ) {
    $captcha = new Captcha( container() );
    $captcha->fields( $args );
}

/**
 * Check a request for a valid captcha
 *
 * @return bool if the current request has valid captcha and empty honeypot
 */
function check_captcha() {
    $captcha = new Captcha( container() );
    return $captcha->verify();
}

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    add_action( 'init', function() {
        $nopriv = is_user_logged_in() ? '' : '_nopriv';
        $re = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) === 'gmcaptcha_reload';
        $action = $re ? 'gmcaptcha_reload' : 'gmcaptcha';
        $method = $re ? 'reload' : 'stream';
        add_action( "wp_ajax{$nopriv}_{$action}", [ new Stream( container() ), $method ] );
    }, 20 );
} else {
    load_plugin_textdomain( 'gmcaptcha', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
