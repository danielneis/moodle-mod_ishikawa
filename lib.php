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
        return $blocks;
    }

    $causes_blocks = get_records("ishikawa_causes_blocks", 'submissionid', $subid);
    $axis_blocks = get_records("ishikawa_axis_blocks", 'submissionid', $subid);
    $consequences_blocks = get_records("ishikawa_consequences_blocks", 'submissionid', $subid);


    foreach ($causes_blocks as $block) {
        $blocks['causes'][$block->nivel_y][$block->nivel_x] = $block;
    }
    foreach ($axis_blocks as $block) {
        $blocks['axis'][$block->nivel_x] = $block;
    }
    foreach ($consequences_blocks as $block) {
        $blocks['consequences'][$block->nivel_y][$block->nivel_x] = $block;
    }
    $blocks['tail_text'] = $submission['tail_text'];
    $blocks['head_text'] = $submission['head_text'];
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
             '<textarea id="ishikawa_tail" cols="25" rows="25">',$blocks['tail_text'],'</textarea>',
           '</td>',
         '<td>',
         '<table id="ishikawa_center">',
            '<tr>',
            '<td><table>';
            if (!$blocks['causes']) {
                echo '<tr>',
                       '<td colspan="4"><h3>Causas</h3></td>',
                     '</tr>',
                     '<tr>',
                      '<td class="add_column"><a href="#">+coluna</a></td>',
                      '<td colspan="3" class="add_line"><a href="#">+ linha</a></td>',
                      '<td class="add_column"><a href="#">+coluna</a></td>',
                    '</tr>';

                for ($i = 0; $i <3; $i++) {
                    echo '<tr><td class="add_column"></td>';
                    for ($j = 0; $j <3; $j++) {
                        echo '<td>',
                             '<textarea name="block[',$i, '][',$j,']" rows="',$rows,'" cols="',$cols,'"></textarea>',
                             '</td>';
                    }
                    echo '</tr>';
                }
                echo '<tr>',
                      '<td colspan="5" class="add_line"><a href="#">+ linha</a></td>',
                     '</tr>';
            } else {
            }
            echo '</table></td></tr>',
                 '<tr id="axis">',
                 '<td>';
            if (!$blocks['axis']) {
                echo '<h3>Eixo</h3>',
                     '<a href="#">+ coluna</a>';
                for ($j = 0; $j <3; $j++) {
                    echo '<textarea name="axis[',$j,']" rows="',$rows,'" cols="',$cols,'"></textarea>';
                }
                echo '<a href="#">+ coluna</a>';
            } else {
            }
            echo '</td></tr>',
                 '<tr>',
                 '<td><table>';
            if (!$blocks['consequences']) {
                echo '<tr>',
                       '<td colspan="4"><h3>Causas</h3></td>',
                     '</tr>',
                     '<tr>',
                      '<td class="add_column"><a href="#">+coluna</a></td>',
                      '<td colspan="3" class="add_line"><a href="#">+ linha</a></td>',
                      '<td class="add_column"><a href="#">+coluna</a></td>',
                    '</tr>';
                for ($i = 0; $i <3; $i++) {
                    echo '<tr><td class="add_column"></td>';
                    for ($j = 0; $j <3; $j++) {
                        echo '<td>',
                             '<textarea name="block[',$i, '][',$j,']" rows="',$rows,'" cols="',$cols,'"></textarea>',
                             '</td>';
                    }
                    echo '</tr>';
                }
                echo '<tr>',
                      '<td colspan="5" class="add_line"><a href="#">+ linha</a></td>',
                      '</tr>';
            } else {
            }
        echo '</table></td></tr>',
             '</table>',
             '</td>',
             '<td class="extremos">',
             '<h2>', get_string('head', 'ishikawa'), '</h2>',
             '<textarea id="ishikawa_head" cols="25" rows="25">',$blocks['head_text'],'</textarea>',
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

?>
