<?php
class Seta {

    var $xi, $xf, $yi, $yf;
    var $draw;
    var $im;

    function __construct($xi, $xf, $yi, $yf, $im, $draw) {
        $this->xi = $xi;
        $this->xf = $xf;
        $this->yi = $yi;
        $this->yf = $yf;
        $this->color = "#000000";
        $this->draw = $draw;
        $this->im = $im;
    }

    function drawLine() {
        $this->draw->setStrokeColor($this->color);
        $this->draw->setFillColor($this->color);

        $this->draw->line($this->xi, $this->yi, $this->xf, $this->yf);
    }

    function drawArrow() {
        $delta_x = $this->xf - $this->xi;
        $delta_y = $this->yf - $this->yi;

        if ($delta_x != 0 && $delta_y != 0) {
            $m = $delta_y/$delta_x;
            if ($delta_x > 0) {
                $angulo = 90 + rad2deg(atan($m));
            } else {
                $angulo = 270 + rad2deg(atan($m));
            }
        } else {
            if ($delta_x == 0 && $delta_y > 0) {
                $angulo = 180;
            } else if ($delta_y == 0 && $delta_x <= 0) {
                $angulo = -90;
            } else  if ($delta_y == 0 && $delta_x > 0) {
                $angulo = 90;
            } else {
                $angulo = 0;
            }
        }

        $seta = new Imagick('seta.png');
        $seta->rotateImage(new ImagickPixel('none'), $angulo);
        $this->im->compositeImage($seta, Imagick::COMPOSITE_DEFAULT, $this->xf - 5, $this->yf - 5);
    }
}
?>
