<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// this file contains all the functions that aren't needed by core moodle
// but start becoming required once we're actually inside the ishikawa module.
/**
 * @package     mod
 * @subpackage  ishikawa
 **/

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
