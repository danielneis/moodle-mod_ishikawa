<?php
class Seta {

    var $xi, $xf, $yi, $yf, $x_delete, $y_delete;
    var $draw;
    var $im;

    var $id = null;

    function __construct($xi, $xf, $yi, $yf, $im, $draw, $id = null) {
        $this->xi = $xi;
        $this->xf = $xf;
        $this->yi = $yi;
        $this->yf = $yf;
        $this->x_delete = $this->xi + (($this->xf - $this->xi) / 2) - 5;
        $this->y_delete = $this->yi + (($this->yf - $this->yi) / 2) - 5;
        $this->color = "#000000";
        $this->draw = $draw;
        $this->im = $im;
        $this->id = $id;
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

    function drawX() {
        $x = new Imagick('x.png');
        $this->im->compositeImage($x, Imagick::COMPOSITE_DEFAULT, $this->x_delete, $this->y_delete);
    }
}
?>
