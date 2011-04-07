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

function ishikawa_blocks_from_submission($subid) {
    $raw_blocks = get_records("ishikawa_blocks", 'submissionid', $subid);

    $blocks = array();

    foreach ($raw_blocks as $block) {
        $blocks[$block->nivel_y][$block->nivel_x] = $block;
    }
    return $blocks;
}

function ishikawa_edit_blocks($cmid, $blocks = array(), $subid = 0) {

    echo '<form action="saveblocks.php" method="POST">',
         '<input type="hidden" name="cmid" value="',$cmid,'" >',
         '<table class="generaltable">';

    if (!$blocks) {
        echo '<tr><th></th>';
        for ($i = 0; $i <3; $i++) {
            echo '<th>Coluna', $i, '</th>';
        }
        echo '</tr>';
        for ($i = 0; $i <3; $i++) {
            echo '<tr><th>Nivel ',$i,'</th>';
            for ($j = 0; $j <3; $j++) {
                echo '<td>',
                     '<textarea name="block[',$i, '][',$j,']"></textarea>',
                     '</td>';
            }
            echo '<td><a href="#">Adicionar coluna</a></td></tr>';
        }
        echo '<tr><td colspan="3"><a href="#">Adicionar linha</a></td></tr>';
    } else {
    }

    echo '</table>';

    if ($subid) {
        echo "<input type='hidden' name='subid' value='{$subid}' />";
    }

    echo '<input type="submit" value="Salvar">',
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
