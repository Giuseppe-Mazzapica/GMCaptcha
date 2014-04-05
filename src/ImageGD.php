<?php

namespace GM;

class ImageGD implements ImageInterface {

    protected $image;

    protected $size;

    protected $tools;

    protected $options;

    private $code;

    protected $color;

    public function __construct( $image, Array $size, Tools $t, $options = [ ] ) {
        if ( ! is_resource( $image ) ) {
            throw new \InvalidArgumentException( 'Invalid GD resource.' );
        }
        if ( ! get_resource_type( $image ) === 'gd' ) {
            throw new \InvalidArgumentException( 'Invalid GD resource.' );
        }
        $this->image = $image;
        if ( function_exists( 'imagelayereffect' ) ) {
            imagelayereffect( $this->image, IMG_EFFECT_ALPHABLEND );
        }
        $this->size = $size;
        if ( ! is_numeric( $size[0] ) || (int) $size[0] <= 0 ) {
            $size[0] = 150;
        }
        if ( ! isset( $size[1] ) || ! is_numeric( $size[1] ) || (int) $size[1] <= 0 ) {
            $size[1] = 100;
        }
        $this->tools = $t;
        $this->options = $options;
    }

    public function setCode( $code ) {
        if ( ! is_string( $code ) ) {
            throw new \InvalidArgumentException;
        }
        $this->code = $code;
    }

    public function setColor() {
        $a = $this->tools->hex2rgb( $this->options['color'] );
        $this->color = imagecolorallocate( $this->image, $a['R'], $a['G'], $a['B'] );
    }

    public function addNoise() {
        if ( isset( $this->options['lines'] ) && is_numeric( $this->options['lines'] ) ) {
            $this->addLines( $this->options['lines'] );
        }
        if ( isset( $this->options['dots'] ) && is_numeric( $this->options['dots'] ) ) {
            $this->addDots( $this->options['dots'] );
        }
    }

    public function addText() {
        $font_size = $this->size[1] * 0.75;
        $font = $this->options['font'];
        $textbox = imagettfbbox( $font_size, 0, $font, $this->code );
        $x = ( $this->size[0] - $textbox[4] ) / 2;
        $y = ( $this->size[1] - $textbox[5] ) / 2;
        $a = [$this->image, $font_size, 0, $x, $y, $this->color, $font, $this->code ];
        imagettftext( $this->image, $font_size, 0, $x, $y, $this->color, $font, $this->code );
    }

    protected function addDots( $dots ) {
        $w = $this->size[0];
        $h = $this->size[1];
        for ( $i = 0; $i < $dots; $i ++ ) {
            imagefilledellipse(
                $this->image, mt_rand( 0, $w ), mt_rand( 0, $h ), 2, 3, $this->color
            );
        }
    }

    protected function addLines( $lines ) {
        $w = $this->size[0];
        $h = $this->size[1];
        for ( $i = 0; $i < $lines; $i ++ ) {
            imageline(
                $this->image, mt_rand( 0, $w ), mt_rand( 0, $h ), mt_rand( 0, $w ), mt_rand( 0, $h ), $this->color
            );
        }
    }

    public function getImage() {
        return $this->image;
    }

}