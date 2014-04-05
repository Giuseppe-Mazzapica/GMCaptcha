<?php

namespace GM;

class ImageImagick implements ImageInterface {

    protected $image;

    protected $size;

    protected $tools;

    protected $options;

    private $code;

    protected $color;

    public function __construct( \Imagick $image, Array $size, $options = [ ] ) {
        $this->image = $image;
        $this->size = $size;
        if ( ! is_numeric( $size[0] ) || (int) $size[0] <= 0 ) {
            $size[0] = 150;
        }
        if ( ! isset( $size[1] ) || ! is_numeric( $size[1] ) || (int) $size[1] <= 0 ) {
            $size[1] = 100;
        }
        $this->options = $options;
    }

    public function setCode( $code ) {
        if ( ! is_string( $code ) ) {
            throw new \InvalidArgumentException;
        }
        $this->code = $code;
    }

    public function setColor() {
        $this->color = '#' . $this->options['color'];
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
        $font_size = $this->size[1];
        $draw = new \ImagickDraw();
        $draw->setFont( $this->options['font'] );
        $draw->setFontSize( $font_size );
        $draw->setFillColor( $this->color );
        $draw->setStrokeAntialias( FALSE );
        $draw->setTextAntialias( FALSE );
        $metrics = $this->getImage()->queryFontMetrics( $draw, $this->code );
        $x = ($this->size[0] - $metrics['textWidth']) / 2;
        $y = $metrics['ascender'] + ($font_size / 10);
        $draw->annotation( $x, $y, $this->code );
        $this->getImage()->drawImage( $draw );
        $this->getImage()->addNoiseImage( \Imagick::NOISE_GAUSSIAN );
    }

    protected function addDots( $dots ) {
        $w = $this->size[0];
        $h = $this->size[1];
        $draw = new \ImagickDraw();
        $draw->setFillColor( $this->color );
        for ( $i = 0; $i < $dots; $i ++ ) {
            $x = mt_rand( 0, $w );
            $y = mt_rand( 0, $h );
            $draw->circle( $x, $y, $x - 1, $y - 1 );
        }
        $this->getImage()->drawImage( $draw );
    }

    protected function addLines( $lines ) {
        $w = $this->size[0];
        $h = $this->size[1];
        $draw = new \ImagickDraw();
        $draw->setStrokeColor( $this->color );
        for ( $i = 0; $i < $lines; $i ++ ) {
            $draw->line(
                mt_rand( 0, $w ), mt_rand( 0, $h ), mt_rand( 0, $w ), mt_rand( 0, $h )
            );
        }
        $this->getImage()->drawImage( $draw );
    }

    public function getImage() {
        return $this->image;
    }

}