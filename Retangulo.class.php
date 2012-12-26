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

class Retangulo {

    var $upper_x, $upper_y, $bottom_x, $bottom_y;

    var $largura = 150;
    var $altura;

    var $im;
    var $draw;

    private $text = '';
    private $text_lines = array();
    private $padding_h = 10;
    private $padding_v = 17;
    private $line_height;
    private $line_spacing = -4;

    private $color;

    function __construct($x, $y, $text, $draw, $canvas, $im, $color = '#ffffdd') {

        $this->draw = $draw;
        $this->canvas = $canvas;
        $this->im = $im;

        $this->color = $color;

        $this->text = $text;
        $this->text_lines = $this->str_in_lines($text, $this->largura);

        $metrics =  $im->queryfontmetrics($this->draw, $text);
        $this->line_height = $metrics['textHeight'];

        $this->altura = count($this->text_lines) * ($this->line_height + $this->line_spacing);

        $this->upper_x = $x;
        $this->upper_y = $y;
        $this->bottom_x = $x + $this->largura + 2 * $this->padding_h;
        $this->bottom_y = $y + $this->altura + $this->padding_v;
    }

    function text() {
        return $this->text;
    }

    static function funcoes() {
        return array('pontoMedioTopo', 'pontoMedioBase', 'pontoMedioLateralDireita', 'pontoMedioLateralEsquerda');
    }

    function color() {
        return $this->color;
    }

    function draw() {
        if (empty($this->text_lines)) {
            return false;
        }
        $this->draw->setFillColor($this->color);
        $this->draw->rectangle($this->upper_x, $this->upper_y, $this->bottom_x, $this->bottom_y);

        $x = $this->upper_x + $this->padding_h;
        $y = $this->upper_y + $this->padding_v;
        foreach ($this->text_lines as $l) {
            $this->canvas->annotation($x, $y, $l);
            $y += $this->line_height + $this->line_spacing;
        }
    }

    function pontoMedioTopo() {
        return array(($this->bottom_x + $this->upper_x) / 2, $this->upper_y);
    }

    function pontoMedioBase() {
        return array(($this->bottom_x + $this->upper_x) / 2, $this->bottom_y);
    }

    function pontoMedioLateralDireita() {
        return array($this->bottom_x, ($this->upper_y + $this->bottom_y)/2);
    }

    function pontoMedioLateralEsquerda() {
        return array($this->upper_x, ($this->upper_y + $this->bottom_y)/2);
    }

    private function str_in_lines($text, $max_width) {

        if (empty($text) && $text != '0') {
            return array();
        }

        $text_tmp = explode(' ', $text);
        $text = '';

        foreach ($text_tmp as $palavra) {
            $text .= $this->quebra_palavra($palavra, 20) . ' ';
        }

        $words = explode(" ", $text);
        $lines = array();
        $i=0;
        while ($i < count($words)) {//as long as there are words

            $line = "";
            do { //append words to line until the fit in size

                if ($line != "") {
                    $line .= " ";
                }
                $line .= $words[$i];

                $i++;

                if (($i) == count($words)) {
                    break; //last word -> break
                }

                //messure size of line + next word
                $linePreview = $line." ".$words[$i];
                $metrics = $this->im->queryFontMetrics($this->canvas, $linePreview);
            } while($metrics["textWidth"] <= $max_width);

            $lines[] = $line;
        }
        return $lines;
    }

    private function quebra_palavra($str, $n) {
        if (strlen($str) < $n) {
            return $str;
        }
        return substr($str, 0, $n) . ' ' . $this->quebra_palavra(substr($str, $n), $n);
    }


    function setAltura($novo_bottom_y) {
        $this->bottom_y = $novo_bottom_y;
    }

    function setUpperY($novo_y) {
        $this->upper_y = $novo_y;
    }
    function setBottomY($novo_y) {
        $this->bottom_y = $novo_y;
    }

    function moveY($n) {
        $this->upper_y += $n;
        $this->bottom_y += $n;
    }

    function pontoMedioY() {
        return (($this->bottom_y - $this->upper_y) / 2) + $this->upper_y;
    }
}

?>
