<?php

namespace GM;

class Stream {

    function __construct( \Pimple $container ) {
        $this->container = $container;
    }

    function stream( $reload = FALSE ) {
        $raw = filter_input( INPUT_GET, 'c', FILTER_SANITIZE_STRING );
        $code = ( ! empty( $raw ) ) ? $this->check( $this->container['tools']->decode( $raw ) ) : FALSE;
        if ( empty( $code ) ) {
            $num = filter_input( INPUT_GET, 'chars_num', FILTER_SANITIZE_NUMBER_INT );
            if ( empty( $num ) ) $num = $this->container['options']['chars_num'];
            while ( empty( $code ) || ( $this->check( $code ) === FALSE ) ) {
                $code = $this->check( $this->container['tools']->random( $num ) );
            }
        }
        $editor = $this->getEditor();
        if ( $editor !== FALSE && ! is_wp_error( $editor ) ) {
            $this->setupContainer();
            $image = $this->getImage( $editor );
            $image->setCode( $code );
            $image->setColor();
            $image->addNoise();
            $image->addText();
            $editor->setImage( $image->getImage() );
            $this->output( $editor, $reload, $code );
        }
        die( '' );
    }

    function reload() {
        $this->stream( TRUE );
    }

    protected function check( $code ) {
        $sane = preg_replace( '/[^23456789abcdefghjkmnpqrstvwxyz]/', '', $code );
        return $sane === $code ? $sane : FALSE;
    }

    protected function output( EditorInterface $editor, $reload = FALSE, $code = NULL ) {
        if ( $reload ) {
            $num = filter_input( INPUT_GET, 'chars_num', FILTER_SANITIZE_NUMBER_INT );
            if ( empty( $num ) ) $num = $this->container['options']['chars_num'];
            $new = $this->container['tools']->random( $num );
            $encoded = $this->container['tools']->encode( $new );
            $verify = $this->container['tools']->encode( $code, TRUE );
            $editor->reload( $verify, $encoded );
        } else {
            $editor->stream();
        }
    }

    protected function getEditor() {
        $class = static::checkEditors();
        if ( $class === FALSE ) return $class;
        $editor = new $class( $this->container['img'] );
        $loaded = $editor->load();
        if ( is_wp_error( $loaded ) ) return $loaded;
        return $editor;
    }

    static function checkEditors() {
        global $wp_filters;
        $now = isset( $wp_filters['wp_image_editors'] ) ? $wp_filters['wp_image_editors'] : NULL;
        if ( ! is_null( $now ) ) unset( $wp_filters['wp_image_editors'] );
        add_filter( 'wp_image_editors', [ __CLASS__, 'getEditors' ] );
        $class = _wp_image_editor_choose();
        remove_filter( 'wp_image_editors', [ __CLASS__, 'getEditors' ] );
        if ( ! is_null( $now ) ) $wp_filters['wp_image_editors'] = $now;
        return in_array( $class, static::getEditors(), TRUE ) ? $class : FALSE;
    }

    static function getEditors() {
        return [ '\GM\EditorImagick', '\GM\EditorGD' ];
    }

    protected function setupContainer() {
        $w = $this->container['size'][0];
        $h = $this->container['size'][1];
        $size = filter_input( INPUT_GET, 'size', FILTER_SANITIZE_STRING );
        if ( ! empty( $size ) ) {
            $s = explode( 'x', $size );
            if ( is_numeric( $s[0] ) && (int) $s[0] > 0 ) $w = (int) $s[0];
            if ( isset( $s[1] ) && is_numeric( $s[1] ) && (int) $s[1] > 0 ) $h = (int) $s[1];
        }
        $d = filter_input( INPUT_GET, 'dots', FILTER_SANITIZE_NUMBER_INT );
        $dots = ( ! is_null( $d ) ) ? (int) $d : $this->container['options']['dots'];
        $l = filter_input( INPUT_GET, 'lines', FILTER_SANITIZE_NUMBER_INT );
        $lines = ( ! is_null( $l ) ) ? (int) $l : $this->container['options']['lines'];
        $c = (string) filter_input( INPUT_GET, 'color', FILTER_SANITIZE_STRING );
        $def_color = $this->container['options']['color'];
        $color = $this->container['tools']->checkColor( $c ) ? : $def_color;
        $this->container['size'] = [$w, $h ];
        $options = ['dots' => $dots, 'lines' => $lines, 'color' => $color ];
        $this->container['options'] = array_merge( $this->container['options'], $options );
    }

    protected function getImage( \WP_Image_Editor $editor ) {
        $editor->resize( $this->container['size'][0], $this->container['size'][1], TRUE );
        $this->container['image'] = $editor->getImage();
        $type = $editor instanceof \WP_Image_Editor_GD ? 'gd' : 'imagick';
        return $this->container[$type];
    }

}