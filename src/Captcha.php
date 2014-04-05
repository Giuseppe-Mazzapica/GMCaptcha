<?php

namespace GM;

/**
 * Handle display and the check of captcha fields
 */
class Captcha {

    protected $container;

    protected $tools;

    private $code;

    protected $chars_num;

    protected $color;

    protected $dots;

    protected $lines;

    protected $width;

    protected $height;

    function __construct( \Pimple $container = NULL ) {
        if ( ! is_null( $container ) ) {
            $this->container = $container;
            $this->tools = $this->container['tools'];
        }
    }

    /**
     * Allow to configure some options, generate a random code and print the fields
     */
    function fields( $args = [ ] ) {
        $good = [ 'chars_num', 'dots', 'lines', 'width', 'height', 'color' ];
        foreach ( $args as $k => $v ) {
            if ( in_array( $k, $good ) ) $this->$k = $v;
        }
        $this->setCode();
        if ( ( $class = apply_filters( 'gmcaptcha_container_class', '' ) ) ) {
            $class = ' class = "' . esc_attr( $class ) . '"';
        }
        echo '<div id="gmcaptcha_container"' . $class . ' data-num="' . $this->chars_num . '">';
        echo $this->getHidden();
        if ( apply_filters( 'gmcaptcha_use_honeypot', TRUE ) ) {
            echo $this->getHoneypot();
        }
        echo $this->getImg();
        echo $this->getText();
        echo '</div>';
    }

    /**
     * Generate a random code
     */
    private function setCode() {
        if ( is_null( $this->chars_num ) ) {
            $this->chars_num = $this->container['options']['chars_num'];
        }
        $this->code = $this->tools->random( $this->chars_num );
    }

    /**
     * Print hidden field
     */
    protected function getHidden() {
        $f = '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />';
        $code = $this->tools->encode( $this->code, TRUE );
        return sprintf( $f, md5( __CLASS__ ) . '_n', $code );
    }

    /**
     * Print text field
     */
    protected function getText() {
        $f = '<label style="margin-left:8px;">%s '
            . '<input type="text" name="%s" value="" autocomplete="off" required /></label>'
            . '<br><small>%s</small>';
        $label = apply_filters( 'gmcaptcha_label', __( 'Type the captcha:', 'gmcaptcha' ) );
        $title = esc_attr( __( 'Click to load another code', 'gmcaptcha' ) );
        return sprintf( $f, $label, md5( __CLASS__ ), $title );
    }

    /**
     * Print captcha image
     */
    protected function getImg() {
        $f = '<a href="#" data-code="%s" class="gmcaptcha" title="%s">'
            . '<img src="%s" width="%d" height="%d" alt="" /></a>';
        $data = $this->prepareData();
        $url = esc_url( add_query_arg( $data, admin_url( 'admin-ajax.php' ) ) );
        $newcode = $this->tools->random( $this->chars_num );
        unset( $data['c'] );
        $c = esc_attr( $this->tools->encode( $newcode ) );
        $data['action'] = 'gmcaptcha_reload';
        $data['chars_num'] = $this->chars_num;
        add_action( 'wp_footer', function() use($data) {
            $this->addScript( $data );
        }, 0 );
        $title = esc_attr( __( 'Click to load another code', 'gmcaptcha' ) );
        return sprintf( $f, $c, $title, $url, $this->width, $this->height );
    }

    /**
     * Print honeypot field
     */
    protected function getHoneypot() {
        $f = '<span style="display:none;!important"><input type="text" name="%s" value="" /></span>';
        return sprintf( $f, md5( __CLASS__ ) . '_h' );
    }

    function addScript( $data ) {
        $script = defined( 'WP_DEBUG' ) && WP_DEBUG ? 'gmcaptcha.js' : 'gmcaptcha.min.js';
        wp_enqueue_script(
            'gmcaptcha', $this->container['url'] . $script, [ 'jquery' ], NULL, TRUE
        );
        $data = [
            'hidden'     => md5( __CLASS__ ) . '_n',
            'ajax_url'   => esc_url( add_query_arg( $data, admin_url( 'admin-ajax.php' ) ) ),
            'ajax_error' => __(
                'Sorry an error occurred on image generation. Please reload the page', 'gmcaptcha'
            )
        ];
        wp_localize_script( 'gmcaptcha', 'gmcaptcha', $data );
    }

    protected function prepareData() {
        $data = [
            'action' => 'gmcaptcha',
            'c'      => $this->tools->encode( $this->code ),
        ];
        if ( ! is_null( $this->width ) && ! is_null( $this->height ) ) {
            $data['size'] = "{$this->width}x{$this->height}";
        } else {
            $this->width = $this->container['size'][0];
            $this->height = $this->container['size'][1];
        }
        if ( ! is_null( $this->dots ) && is_numeric( $this->dots ) ) {
            $data['dots'] = "{$this->dots}";
        }
        if ( ! is_null( $this->lines ) && is_numeric( $this->lines ) ) {
            $data['lines'] = "{$this->lines}";
        }
        if ( ! is_null( $this->color ) && is_string( $this->color ) ) {
            $data['color'] = "{$this->color}";
        }
        return $data;
    }

    /**
     * Verify the captcha and the honeypot
     */
    function verify() {
        $method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );
        $type = strtoupper( $method ) === 'GET' ? INPUT_GET : INPUT_POST;
        $honeypot = filter_input( $type, md5( __CLASS__ ) . '_h', FILTER_SANITIZE_STRING );
        if ( ! empty( $honeypot ) ) return FALSE;
        $encr = filter_input( $type, md5( __CLASS__ ) . '_n', FILTER_SANITIZE_STRING );
        $clean = strtolower( filter_input( $type, md5( __CLASS__ ), FILTER_SANITIZE_STRING ) );
        $code = $this->tools->encode( $clean, TRUE );
        return ! empty( $encr ) && $code === $encr;
    }

}