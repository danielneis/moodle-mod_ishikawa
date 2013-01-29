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

require_once('Retangulo.class.php');
require_once('seta.class.php');

class Ishikawa  {

    var $retangulos = array();

    var $setas = array();

    var $blocks;

    var $inicio_x = 10;
    var $inicio_y = 10;

    var $offset = 50;

    var $im;
    var $draw;

    var $colors = array('#ffff00', '#ffd100', '#ff8b00', '#ff5c00', '#ff2200', '#da0000');

    var $header = '';

    var $footer = '';

    function __construct($blocks, $connections, $src_id = 0, $src_type = null, $header = null, $footer = null) {

        $this->blocks = $blocks;
        $this->connections = $connections;

        $this->src_id = $src_id;
        $this->src_type = $src_type;

        $this->im = new Imagick();
        $this->draw = new ImagickDraw();

        $this->canvas = new ImagickDraw();
        // SUGGESTION: implement font list config in config_plugins
        $fontfile = './Ubuntu-R.ttf';
        if (file_exists($fontfile)) {
            $this->canvas->setFont($fontfile);
        }

        $this->header = get_string('modulename', 'ishikawa');
        if (!is_null($header)) {
            $this->header .= " - {$header}";
        }

        $this->footer = $footer;
    }

    function retangulos() {
        $this->header();
        $this->generate_blocks();
        return $this->retangulos;
    }

    function setas() {
        $this->generate_connections();
        return $this->setas;
    }

    private function generate_blocks() {
        $this->generate_tail();

        $this->generate_multinivel('causes');

        $this->generate_axis();

        $this->generate_multinivel('consequences');

        $this->generate_head();
    }

    private function header() {
        $this->ponto_x_atual = $this->inicio_x;
        $this->ponto_y_atual = $this->inicio_y;

        $metrics = $this->im->queryFontMetrics($this->canvas, $this->header);
        $this->ponto_y_atual += $metrics['textHeight'];
    }

    function draw($edit = false, $download = false) {

        $this->header();

        $this->generate_blocks();

        $this->generate_connections();

        foreach ($this->blocks['axis'] as $block) {
            if (empty($block->texto) && $block->texto != '0') {
                continue;
            }
            break;
        }

        reset($this->retangulos['axis']);
        $this->retangulos['tail']->setUpperY(current($this->retangulos['axis'])->upper_y - 95);
        $this->retangulos['tail']->setBottomY(current($this->retangulos['axis'])->bottom_y + 95);
        $this->retangulos['head']->setUpperY(current($this->retangulos['axis'])->upper_y - 95);
        $this->retangulos['head']->setBottomY(current($this->retangulos['axis'])->bottom_y + 95);

        $this->geraSeta($this->retangulos['tail'], $this->retangulos['axis'][$block->id]);

        $reverse_axis = array_reverse($this->blocks['axis']);

        foreach ($reverse_axis as $block) {
            if (empty($block->texto) && $block->texto != '0') {
                continue;
            }
            break;
        }

        $this->geraSeta($this->retangulos['axis'][$block->id], $this->retangulos['head']);

        $this->printme($edit, $download);
    }

    private function generate_connections() {
        global $DB;
        foreach ($this->connections as $id => $connection) {
            $src_text = $this->retangulos[$connection->src_type][$connection->src_id]->text();
            $dst_text = $this->retangulos[$connection->dst_type][$connection->dst_id]->text();
            if ((!empty($src_text) && $src_text != '0') &&
                (!empty($dst_text) && $dst_text != '0')) {
                $this->geraSeta($this->retangulos[$connection->src_type][$connection->src_id],
                                $this->retangulos[$connection->dst_type][$connection->dst_id], $connection->id);
            }
        }

        $axis_blocks = array();
        foreach ($this->retangulos['axis'] as $block) {
            $text = $block->text();
            if (!empty($text) && $text != '0') {
                $axis_blocks[] = $block;
            }
        }
        $count_axis_blocks = count($axis_blocks);
        for ($i = 1; $i < $count_axis_blocks; $i++) {
            $this->geraSeta($axis_blocks[$i-1], $axis_blocks[$i]);
        }
    }

    private function printme($edit = false, $download = false) {

        $this->canvas->setFontSize(15);

        $this->canvas->annotation($this->inicio_x, $this->inicio_y + 5, $this->header);

        $this->canvas->setFontSize(12);

        $this->retangulos['tail']->draw();

        $parts = array('causes', 'axis', 'consequences');
        foreach ($parts as $p) {
            foreach($this->retangulos[$p] as $retangulo) {
                $retangulo->draw();
            }
        }

        $this->retangulos['head']->draw();

        foreach ($this->setas as $seta) {
            $seta->drawLine();
        }

        if ($this->footer) {
            $this->canvas->setFontSize(15);
            $metrics = $this->im->queryFontMetrics($this->draw, $this->footer);
            $this->canvas->annotation($this->inicio_x, $this->ponto_y_maximo - $metrics['textHeight'], $this->footer);
        }

        $this->im->newImage($this->ponto_x_maximo, $this->ponto_y_maximo, new ImagickPixel('lightgray'));
        $this->im->drawImage($this->draw);
        $this->im->drawImage($this->canvas);

        $this->im->setImageFormat('png');    // Give the image a format

        foreach ($this->setas as $seta) {
            $seta->drawArrow();
        }
        if ($edit) {
            foreach ($this->setas as $seta) {
                if (!is_null($seta->id)) {
                    $seta->drawX();
                }
            }
        }

        if ($download) {
            header('Content-type: image/forcedownload');     // Prepare the web browser to display an image
            header("Content-Disposition: filename=espinha.png"); // use 'attachment' to force a download
        } else {
            header('Content-type: image/png');     // Prepare the web browser to display an image
        }
        echo $this->im;                // Publish it to the world!
    }

    private function geraSeta($origem, $destino, $id = null) {
        $menor_comprimento_reta = 9999;
        foreach (Retangulo::funcoes() as $funcao1) {
            foreach (Retangulo::funcoes() as $funcao2) {
                list($xi_tmp, $yi_tmp) = call_user_func(array($origem, $funcao1));
                list($xf_tmp, $yf_tmp) = call_user_func(array($destino, $funcao2));
                $comprimento_reta_tmp = $this->comprimentoReta($xi_tmp, $xf_tmp, $yi_tmp, $yf_tmp);

                if ($comprimento_reta_tmp < $menor_comprimento_reta) {
                    $xi = $xi_tmp;
                    $xf = $xf_tmp;
                    $yi = $yi_tmp;
                    $yf = $yf_tmp;
                    $menor_comprimento_reta = $comprimento_reta_tmp;
                }
            }
        }

        $this->setas[] = new Seta($xi, $xf, $yi, $yf, $this->im, $this->draw, $id);
    }

    private function comprimentoReta($xi, $xf, $yi, $yf) {
        return sqrt(pow($xf-$xi, 2) + pow($yf-$yi,2));
    }

    private function generate_head() {
        $this->ponto_y_atual = $this->inicio_y;
        $this->ponto_x_atual = $this->ponto_x_head;
        $this->retangulos['head'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['head_text'], $this->draw, $this->canvas, $this->im);
        $this->ponto_x_atual = $this->retangulos['head']->bottom_x + $this->offset;
        $this->ponto_x_maximo = $this->ponto_x_atual;
    }

    private function generate_tail() {
        $this->retangulos['tail'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['tail_text'], $this->draw, $this->canvas, $this->im);
        $this->ponto_x_atual += $this->retangulos['tail']->bottom_x + $this->offset;
        $this->ponto_x_tail = $this->ponto_x_atual;
    }

    private function generate_multinivel($multinivel) {
        $maior_altura = 0;
        foreach ($this->blocks[$multinivel] as $nivel_y => $bls) {
            foreach ($bls as $nivel_x => $block) {

                if ($this->src_type == $multinivel && $this->src_id == $block->id) {
                    $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->canvas, $this->im, "green");
                } else {
                    $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->canvas, $this->im, $this->colors[$nivel_y % sizeof($this->colors)]);
                }
                $this->ponto_x_atual = $retangulo->bottom_x + $this->offset;

                if ($retangulo->bottom_y > $maior_altura) {
                    $maior_altura = $retangulo->bottom_y;
                }

                $this->retangulos[$multinivel][$block->id] = $retangulo;
            }
            $this->ponto_y_atual = $maior_altura + $this->offset;
            $this->ponto_x_head = $this->ponto_x_atual;
            $this->ponto_x_atual = $this->ponto_x_tail;
        }
        $this->ponto_y_maximo = $this->ponto_y_atual;
    }

    private function generate_axis() {
        $maior_retangulo = new stdclass();
        $maior_retangulo->bottom_y = 0;

        foreach ($this->blocks['axis'] as $nivel_x => $block) {

            if ($this->src_type == 'axis' && $this->src_id == $block->id) {
                $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->canvas, $this->im, "green");
            } else {
                $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->canvas, $this->im);
            }
            $this->ponto_x_atual = $retangulo->bottom_x + $this->offset;

            if ($retangulo->bottom_y > $maior_retangulo->bottom_y) {
                 $maior_retangulo = $retangulo;
            }

            $this->retangulos['axis'][$block->id] = $retangulo;
        }
        $this->ponto_x_atual = $this->ponto_x_tail;
        $this->ponto_y_atual = $maior_retangulo->bottom_y + $this->offset;

        foreach ($this->retangulos['axis'] as $nivel_x => $retangulo) {
            $retangulo->moveY($maior_retangulo->pontoMedioY() - $retangulo->pontoMedioY());
        }
    }
}
?>
