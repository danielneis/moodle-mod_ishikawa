<?php
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

    function __construct($blocks, $connections, $src_id = 0, $src_type = null, $user = null) {

        $this->blocks = $blocks;
        $this->connections = $connections;

        $this->src_id = $src_id;
        $this->src_type = $src_type;

        $this->im = new Imagick();
        $this->draw = new ImagickDraw();

        $this->user = $user;

        $this->draw->setFillColor('white');
        $this->draw->setStrokeColor(new ImagickPixel('black'));
    }

    function retangulos() {
        $this->generate_blocks();
        return $this->retangulos;
    }

    function setas() {
        $this->generate_connections();
        return $this->setas;
    }

    private function generate_blocks() {
        $this->ponto_x_atual = $this->inicio_x;
        $this->ponto_y_atual = $this->inicio_y;

        $this->generate_tail();

        $this->generate_multinivel('causes');

        $this->generate_axis();

        $this->generate_multinivel('consequences');

        $this->generate_head();
    }

    function draw($edit = false) {

        $title = "Diagrama de Ishikawa";
        $this->draw->setFontSize(15);
        $metrics = $this->im->queryFontMetrics($this->draw, $title);
        $this->inicio_y += 15;
        $this->draw->annotation($this->inicio_x, $this->inicio_y, $title);
        $this->inicio_y += $metrics['textHeight'] + 5;

        $this->draw->setFontSize(12);

        if ($this->user) {
            $metrics = $this->im->queryFontMetrics($this->draw, $this->user);
            $this->draw->annotation($this->inicio_x, $this->inicio_y, $this->user);
            $this->inicio_y += $metrics['textHeight'] + 5;
        }

        $this->generate_blocks();

        if ($this->retangulos['tail']->bottom_y < $this->ponto_y_maximo) {
            $this->retangulos['tail']->setAltura($this->ponto_y_maximo - $this->offset);
        } else {
            $this->ponto_y_maximo = $this->retangulos['tail']->bottom_y + $this->offset;
        }

        if ($this->retangulos['head']->bottom_y < $this->ponto_y_maximo) {
            $this->retangulos['head']->setAltura($this->ponto_y_maximo - $this->offset);
        } else {
            $this->ponto_y_maximo = $this->retangulos['head']->bottom_y + $this->offset;
        }

        $this->generate_connections();

        foreach ($this->blocks['axis'] as $block) {
            if (empty($block->texto) && $block->texto != '0') {
                continue;
            }
            break;
        }

        $this->geraSeta($this->retangulos['tail'], $this->retangulos['axis'][$block->id]);

        $reverse_axis = array_reverse($this->blocks['axis']);

        foreach ($reverse_axis as $block) {
            if (empty($block->texto) && $block->texto != '0') {
                continue;
            }
            break;
        }

        $this->geraSeta($this->retangulos['axis'][$block->id], $this->retangulos['head']);

        $this->printme($edit);
    }

    private function generate_connections() {
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

    private function printme($edit = false) {

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

        $altura = max($this->ponto_y_maximo, $this->retangulos['tail']->bottom_y, $this->retangulos['head']->bottom_y);

        $this->im->newImage($this->ponto_x_maximo, $altura, new ImagickPixel('lightgray'));
        $this->im->drawImage($this->draw);    // Apply the stuff from the draw class to the image canvas

        $this->im->setImageFormat('jpg');    // Give the image a format

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

        header('Content-type: image/jpeg');     // Prepare the web browser to display an image
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
        $this->retangulos['head'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['head_text'], $this->draw, $this->im);
        $this->ponto_x_atual = $this->retangulos['head']->bottom_x + $this->offset;
        $this->ponto_x_maximo = $this->ponto_x_atual;
    }

    private function generate_tail() {
        $this->retangulos['tail'] = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $this->blocks['tail_text'], $this->draw, $this->im);
        $this->ponto_x_atual += $this->retangulos['tail']->bottom_x + $this->offset;
        $this->ponto_x_tail = $this->ponto_x_atual;
    }

    private function generate_multinivel($multinivel) {
        $maior_altura = 0;
        foreach ($this->blocks[$multinivel] as $nivel_y => $bls) {
            foreach ($bls as $nivel_x => $block) {

                if ($this->src_type == $multinivel && $this->src_id == $block->id) {
                    $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im, "green");
                } else {
                    $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im, $this->colors[$nivel_y]);
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
        $maior_altura = 0;
        foreach ($this->blocks['axis'] as $nivel_x => $block) {

            if ($this->src_type == 'axis' && $this->src_id == $block->id) {
                $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im, "green");
            } else {
                $retangulo = new Retangulo($this->ponto_x_atual, $this->ponto_y_atual, $block->texto, $this->draw, $this->im);
            }
            $this->ponto_x_atual = $retangulo->bottom_x + $this->offset;

            if ($retangulo->bottom_y > $maior_altura) {
                $maior_altura = $retangulo->bottom_y;
            }

            $this->retangulos['axis'][$block->id] = $retangulo;
        }
        $this->ponto_x_atual = $this->ponto_x_tail;
        $this->ponto_y_atual = $maior_altura + $this->offset;
    }
}
?>
