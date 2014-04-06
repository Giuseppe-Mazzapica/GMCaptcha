<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GM;

class Tools {

    private $iv;

    private $key;

    function checkColor( $color ) {
        if ( ! is_string( $color ) ) {
            return FALSE;
        }
        $color = strtoupper( preg_replace( '/[^abcdef0-9]/i', '', $color ) );
        if ( strlen( $color ) !== 3 && strlen( $color ) !== 6 ) {
            return FALSE;
        }
        if ( strlen( $color ) === 3 ) {
            $color = $color{0} . $color{0} . $color{1} . $color{1} . $color{2} . $color{2};
        }
        return $color;
    }

    function hex2rgb( $color ) {
        $color = $this->checkColor( $color );
        if ( $color ) {
            $int = hexdec( "0x{$color}" );
            return [ "R" => 0xFF & ($int >> 0x10), "G" => 0xFF & ($int >> 0x8), "B" => 0xFF & $int ];
        } else {
            return [ "R" => 0, "G" => 0, "B" => 0 ];
        }
    }

    function random( $num = 5 ) {
        $letters = '23456789abcdefghjkmnpqrstvwxyz';
        $len = strlen( $letters ) - 1;
        $i = 0;
        $code = '';
        while ( $i < $num ) {
            $code .= substr( $letters, mt_rand( 0, $len ), 1 );
            $i ++;
        }
        return $code;
    }

    /**
     * Encode the code to avoid put plan code in image url
     */
    function encode( $code, $verify = FALSE ) {
        $iv_size = mcrypt_get_iv_size( MCRYPT_BLOWFISH, MCRYPT_MODE_ECB );
        $iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
        $key = $this->getKey( $verify );
        $enc = mcrypt_encrypt( MCRYPT_BLOWFISH, $key, $code, MCRYPT_MODE_ECB, $iv );
        return trim( urlencode( base64_encode( $enc ) ), ' %3D' );
    }

    /**
     * Decode the code
     */
    function decode( $encoded ) {
        $iv_size = mcrypt_get_iv_size( MCRYPT_BLOWFISH, MCRYPT_MODE_ECB );
        $iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
        $key = $this->getKey();
        $code = urldecode( base64_decode( $encoded ) );
        return mcrypt_decrypt( MCRYPT_BLOWFISH, $key, $code, MCRYPT_MODE_ECB, $iv );
    }

    protected function getKey( $verify = FALSE ) {
        $i = wp_nonce_tick();
        $key = $verify ? strrev( md5( __CLASS__ ) ) : md5( __CLASS__ );
        return substr( wp_hash( $i . $key, 'nonce' ), -12, 10 );
    }

}