<?php

function ishikawa_add_instance($ishi) {
    return insert_record('ishikawa', $ishi);
}

function ishikawa_update_instance($ishi) {

    $ishi->timemodified = time();
    $ishi->id = $ishi->instance;
    
    return update_record("ishikawa", $ishi);
}

function ishikawa_get_submission($userid, $ishikawaid) {
    return get_record('ishikawa_submissions', 'userid', $userid, 'ishikawaid', $ishikawaid);
}

function ishikawa_blocks_from_submission($submission = false) {

    $blocks = array();
    $blocks['causes'] = array();
    $blocks['axis'] = array();
    $blocks['consequences'] = array();
    $blocks['tail_text'] = '';
    $blocks['head_text'] = '';

    if (!$submission){
        $null_block = new stdClass();
        $null_block->submission_id = 0;
        $null_block->texto = '';

        for ($i = 0; $i < 3; $i++) {
            $null_block->nivel_x = $i;
            for ($j = 0; $j < 3; $j++) {
                $null_block->nivel_y = $j;
                $blocks['causes'][$i][$j] = $null_block;
                $blocks['consequences'][$i][$j] = $null_block;
            }
        }
        unset($null_block->nivel_y);
        for ($i = 0; $i < 3; $i++) {
            $null_block->nivel_x = $i;
            $blocks['axis'][$i] = $null_block;
        }

        return $blocks;
    }

    $causes_blocks = get_records("ishikawa_causes_blocks", 'submissionid', $submission->id);
    $axis_blocks = get_records("ishikawa_axis_blocks", 'submissionid', $submission->id);
    $consequences_blocks = get_records("ishikawa_consequences_blocks", 'submissionid', $submission->id);


    foreach ($causes_blocks as $block) {
        $blocks['causes'][$block->nivel_y][$block->nivel_x] = $block;
    }
    foreach ($axis_blocks as $block) {
        $blocks['axis'][$block->nivel_x] = $block;
    }
    foreach ($consequences_blocks as $block) {
        $blocks['consequences'][$block->nivel_y][$block->nivel_x] = $block;
    }
    $blocks['tail_text'] = $submission->tail_text;
    $blocks['head_text'] = $submission->head_text;
    return $blocks;
}

function ishikawa_edit_blocks($cmid, $blocks, $submission) {

    $rows = 3;
    $cols = 15;

    echo '<form action="saveblocks.php" method="post">',
         '<p><input type="hidden" name="cmid" value="',$cmid,'" /></p>',
         '<table class="generaltable">',
         '<tr>',
           '<td class="extremos">',
             '<h2>', get_string('tail', 'ishikawa'), '</h2>',
             '<textarea name="tail_text" id="ishikawa_tail" cols="25" rows="25">',$blocks['tail_text'],'</textarea>',
           '</td>',
         '<td>',
         '<table id="ishikawa_center">',
    // START CAUSES
         '<tr><td><table>',
           '<tr><td colspan="4"><h3>Causas</h3></td></tr>',
           '<tr>',
             '<td class="add_column"><a href="#">+coluna</a></td>',
             '<td colspan="3" class="add_line"><a href="#">+ linha</a></td>',
             '<td class="add_column"><a href="#">+coluna</a></td>',
           '</tr>';

    foreach ($blocks['causes'] as $nivel_y => $causes) {
        echo '<tr><td class="add_column"></td>';
        foreach ($causes as $nivel_x => $b) {
            echo '<td>',
                 '<textarea name="causes[',$nivel_y, '][',$nivel_x,']" rows="',$rows,'" cols="',$cols,'">',
                 $b->texto,
                 '</textarea>',
                 '</td>';
        }
        echo '</tr>';
    }
    echo '<tr><td colspan="5" class="add_line"><a href="#">+ linha</a></td></tr>',
         '</table></td></tr>';

    // END CAUSES - START AXIS
    echo '<tr id="axis"><td>',
          '<h3>Eixo</h3>',
          '<a href="#">+ coluna</a>';
    foreach ($blocks['axis'] as $nivel_x => $b) {
        echo '<textarea name="axis[',$nivel_x,']" rows="',$rows,'" cols="',$cols,'">',
             $b->texto,
             '</textarea>';
    }
    echo '<a href="#">+ coluna</a>',
         '</td></tr>';

    // END AXIS - START CONSEQUENCES
    echo '<tr><td><table>',
           '<tr><td colspan="4"><h3>Consequências</h3></td></tr>',
           '<tr>',
             '<td class="add_column"><a href="#">+coluna</a></td>',
             '<td colspan="3" class="add_line"><a href="#">+ linha</a></td>',
             '<td class="add_column"><a href="#">+coluna</a></td>',
           '</tr>';
    foreach ($blocks['consequences'] as $nivel_y => $consequences) {
        echo '<tr><td class="add_column"></td>';
        foreach ($consequences as $nivel_x => $b) {
            echo '<td>',
                 '<textarea name="consequences[',$nivel_y, '][',$nivel_x,']" rows="',$rows,'" cols="',$cols,'">',
                 $b->texto,
                 '</textarea>',
                 '</td>';
        }
        echo '</tr>';
    }
    echo '<tr><td colspan="5" class="add_line"><a href="#">+ linha</a></td></tr>',
         '</table></td></tr>',
         // END CONSEQUENCES
         '</table>',
         '</td>',
         '<td class="extremos">',
             '<h2>', get_string('head', 'ishikawa'), '</h2>',
             '<textarea name="head_text" id="ishikawa_head" cols="25" rows="25">',$blocks['head_text'],'</textarea>',
         '</td>',
         '</tr>',
         '</table>';

    if ($submission) {
        echo "<p><input type='hidden' name='subid' value='{$submission->id}' /></p>";
    }

    echo '<p><input type="submit" value="Salvar"/></p>',
         '</form>';
}

function ishikawa_view_dates($ishikawa) {
    global $USER, $CFG;

    if (!$ishikawa->timeavailable && !$ishikawa->timedue) {
        return;
    }

    print_simple_box_start('center', '', '', 0, 'generalbox', 'dates');
    echo '<table>';
    if ($ishikawa->timeavailable) {
        echo '<tr><td class="c0">'.get_string('availabledate','ishikawa').':</td>';
        echo '    <td class="c1">'.userdate($ishikawa->timeavailable).'</td></tr>';
    }
    if ($ishikawa->timedue) {
        echo '<tr><td class="c0">'.get_string('duedate','ishikawa').':</td>';
        echo '    <td class="c1">'.userdate($ishikawa->timedue).'</td></tr>';
    }
    $submission = ishikawa_get_submission($USER->id, $ishikawa->id);
    if ($submission) {
        //TODO incluir link para ver/editar o diagrama
        echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
        echo '    <td class="c1">'.userdate($submission->timemodified);
    }
    echo '</table>';
    print_simple_box_end();
}

function ishikawa_get_link_to_block($block, $cmid,$src, $src_type, $dst, $dst_type) {
    global $CFG;

    $link = $CFG->wwwroot.'/mod/ishikawa/createconnections.php?id='.$cmid;

    if ($src) {
        $src_type = required_param('src_type', PARAM_ALPHA);
        $link .= '&src='.$src.'&dst='.$block->id.'&src_type='.$src_type.'&dst_type='.$dst_type;
        $nome = 'Destino';
    } else {
        $link .= '&src='.$block->id.'&src_type='.$src_type;
        $nome = 'Origem';
    }

    return '<a href="'.$link.'">'.$nome.'</a>';
}

function ishikawa_edit_links($cmid, $blocks, $submission, $src, $dst) {

    $rows = 3;
    $cols = 15;

    echo '<form action="saveblocks.php" method="post">',
         '<p><input type="hidden" name="cmid" value="',$cmid,'" /></p>',
         '<table class="generaltable">',
         '<tr>',
           '<td class="extremos">',
             '<h2>', get_string('tail', 'ishikawa'), '</h2>',
             $blocks['tail_text'],
           '</td>',
         '<td>',
         '<table id="ishikawa_center">',
    // START CAUSES
         '<tr><td><table>',
           '<tr><td colspan="4"><h3>Causas</h3></td></tr>';
    foreach ($blocks['causes'] as $nivel_y => $causes) {
        echo '<tr><td class="add_column"></td>';
        foreach ($causes as $nivel_x => $b) {
            echo '<td>',
                 '<p>', $b->texto, '</p>',
                 ishikawa_get_link_to_block($b, $cmid, $src, 'causes', $dst, 'causes'),
                 '</td>';
        }
        echo '</tr>';
    }
    echo '</table></td></tr>';

    // END CAUSES - START AXIS
    echo '<tr id="axis"><td><table>',
         '<tr>',
           '<td><h3>Eixo</h3></td>',
         '</tr>',
         '<tr>';
        foreach ($blocks['axis'] as $nivel_x => $b) {
            echo '<td>',
                 '<p>', $b->texto, '</p>',
                 ishikawa_get_link_to_block($b, $cmid, $src, 'axis', $dst, 'axis'),
                 '</td>';
        }
    echo '</tr></table></td></tr>';

    // END AXIS - START CONSEQUENCES
    echo '<tr><td><table>',
           '<tr><td colspan="4"><h3>Consequências</h3></td></tr>';
    foreach ($blocks['consequences'] as $nivel_y => $consequences) {
        echo '<tr><td class="add_column"></td>';
        foreach ($consequences as $nivel_x => $b) {
            echo '<td>',
                 '<p>', $b->texto, '</p>',
                 ishikawa_get_link_to_block($b, $cmid, $src, 'consequences', $dst, 'consequences'),
                 '</td>';
        }
        echo '</tr>';
    }
    echo '</table></td></tr>',
         // END CONSEQUENCES
         '</table>',
         '</td>',
         '<td class="extremos">',
             '<h2>', get_string('head', 'ishikawa'), '</h2>',
             '<p>',$blocks['head_text'],'</p>',
         '</td>',
         '</tr>',
         '</table>';

    if ($submission) {
        echo "<p><input type='hidden' name='subid' value='{$submission->id}' /></p>";
    }

    echo '<p><input type="submit" value="Salvar"/></p>',
         '</form>';
}

?>
