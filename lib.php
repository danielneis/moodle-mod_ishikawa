<?php
function ishikawa_add_instance($ishi) {
    $id =  insert_record('ishikawa', $ishi);

    $params = array('itemname'=>$ishi->name);

    grade_update('mod/ishikawa', $ishi->course, 'mod', 'ishikawa', $id, 0, null, $params);

    return $id;
}

function ishikawa_update_instance($ishi) {

    $ishi->timemodified = time();
    $ishi->id = $ishi->instance;

    return update_record("ishikawa", $ishi);
}

function ishikawa_delete_instance($ishi) {

    $submissions = get_record('ishikawa_submissions', 'ishikawaid', $ishi);
    foreach ($submissions as $sub) {
        delete_records('ishikawa_axis_blocks', 'submissionid', $sub['id']);
        delete_records('ishikawa_causes_blocks', 'submissionid', $sub['id']);
        delete_records('ishikawa_connections', 'submissionid', $sub['id']);
        delete_records('ishikawa_consequences_blocks', 'submissionid', $sub['id']);
        delete_records('ishikawa_submissions', 'id', $sub['id']);
    }
    delete_records('ishikawa', 'id', $ishi);
}

function ishikawa_get_submission($userid, $ishikawaid) {
    return get_record('ishikawa_submissions', 'userid', $userid, 'ishikawaid', $ishikawaid);
}

function ishikawa_get_grade($userid, $ishikawaid) {
    return get_record('ishikawa_grades', 'userid', $userid, 'ishikawaid', $ishikawaid);
}

function ishikawa_blocks_from_submission($submission = false, $ishikawa = false) {

    $blocks = array();
    $blocks['causes'] = array();
    $blocks['axis'] = array();
    $blocks['consequences'] = array();
    $blocks['tail_text'] = '';
    $blocks['head_text'] = '';

    if (!$submission){
        if (!$ishikawa) {
            print_error('code-error.please, contact the vendors of this module.');
        }

        $null_block = new stdClass();
        $null_block->submission_id = 0;
        $null_block->texto = '';

        for ($i = 0; $i < $ishikawa->maxlines; $i++) {
            $null_block->nivel_x = $i;
            for ($j = 0; $j < $ishikawa->maxcolumns; $j++) {
                $null_block->nivel_y = $j;
                $blocks['causes'][$i][$j] = $null_block;
                $blocks['consequences'][$i][$j] = $null_block;
            }
        }
        unset($null_block->nivel_y);
        for ($i = 0; $i < $ishikawa->maxcolumns; $i++) {
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

function ishikawa_connections_from_submission($submission) {
    if ($r = get_records("ishikawa_connections", "submissionid", $submission->id)) {
        return $r;
    } else {
        return array();
    }
}

function ishikawa_edit_blocks($cmid, $blocks, $submission) {

    $rows = 3;
    $cols = 15;

    echo '<h2>',get_string('first_step', 'ishikawa'), '</h2>',
         '<form action="saveblocks.php" method="post">',
         '<p><input type="hidden" name="cmid" value="',$cmid,'" /></p>',
         '<table class="generaltable">',
         '<tr>',
           '<td class="extremos">',
             '<h2>', get_string('tail', 'ishikawa'), '</h2>',
             '<textarea name="tail_text" id="ishikawa_tail" cols="25" rows="15">',$blocks['tail_text'],'</textarea>',
           '</td>',
         '<td>',
         '<table id="ishikawa_center">',

    '<tr>', '<td colspan="4"><h3>',get_string('causes', 'ishikawa'), '</h3></td>', '</tr>';
    foreach ($blocks['causes'] as $nivel_y => $causes) {
        echo '<tr>';
        foreach ($causes as $nivel_x => $b) {
            $c_name = "causes[{$nivel_y}][{$nivel_x}]";
            echo '<td>';
            if (isset($b->id) and $b->id >0) {
                 echo '<input type="hidden" name="',$c_name,'[id]" value="',$b->id,'">';
            }
            echo '<textarea name="',$c_name,'[texto]" rows="',$rows,'" cols="',$cols,'">',
                 $b->texto,
                 '</textarea>',
                 '</td>';
        }
        echo '</tr>';
    }

    echo '<tr id="axis">', '<td colspan="4"><h3>',get_string('axis', 'ishikawa'), '</h3></td>', '</tr>',
         '<tr>';
    foreach ($blocks['axis'] as $nivel_x => $b) {
        $a_name = "axis[{$nivel_x}]";
        if (isset($b->id) and $b->id >0) {
             echo '<input type="hidden" name="',$a_name,'[id]" value="',$b->id,'">';
        }
        echo '<td><textarea name="',$a_name,'[texto]" rows="',$rows,'" cols="',$cols,'">',
             $b->texto,
             '</textarea></td>';
    }
    echo '</tr>';

    echo '<tr><td colspan="4"><h3>',get_string('consequences', 'ishikawa'),'</h3></td></tr>';
    foreach ($blocks['consequences'] as $nivel_y => $consequences) {
        echo '<tr>';
        foreach ($consequences as $nivel_x => $b) {
            $c_name = "consequences[{$nivel_y}][{$nivel_x}]";
            echo '<td>';
            if (isset($b->id) and $b->id >0) {
                 echo '<input type="hidden" name="',$c_name,'[id]" value="',$b->id,'">';
            }
            echo '<textarea name="',$c_name,'[texto]" rows="',$rows,'" cols="',$cols,'">',
                 $b->texto,
                 '</textarea>',
                 '</td>';
        }
        echo '</tr>';
    }
    echo '</table>',
         '</td>',
         '<td class="extremos">',
             '<h2>', get_string('head', 'ishikawa'), '</h2>',
             '<textarea name="head_text" id="ishikawa_head" cols="25" rows="15">',$blocks['head_text'],'</textarea>',
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
        echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
        echo '    <td class="c1">'.userdate($submission->timemodified);
    }
    echo '</table>';
    print_simple_box_end();
}

function ishikawa_get_link_to_block($block, $cmid,$src, $src_type, $dst, $dst_type) {
    global $CFG;

    $link = $CFG->wwwroot.'/mod/ishikawa/connections.php?id='.$cmid;

    if ($src) {
        $src_type = required_param('src_type', PARAM_ALPHA);
        $link .= '&src='.$src.'&dst='.$block->id.'&src_type='.$src_type.'&dst_type='.$dst_type;
        $nome = 'Destino';
    } else {
        $link .= '&src='.$block->id.'&src_type='.$src_type;
        $nome = 'Origem';
    }

    return $link;
}

function ishikawa_get_link_to_delete_connection($cmid, $connection_id) {
    global $CFG;

    return $CFG->wwwroot.'/mod/ishikawa/connections.php?id=' . $cmid . '&delete_connection=' . $connection_id;
}

function ishikawa_edit_connections($cmid, $blocks, $connections, $submission, $src, $src_type, $dst) {
    global $CFG, $USER;

    require_once("Ishikawa.class.php");

    echo '<h2>', get_string('second_step', 'ishikawa'), '</h2>';

    if ($src) {
        echo '<h3>', get_string('select_dst', 'ishikawa', $cmid), '</h3>';
    } else {
        echo '<h3>', get_string('select_src', 'ishikawa'), '</h3>',
             '<p><a href="view.php?id=',$cmid,'" >',get_string('finish_editing', 'ishikawa'), '</a></p>',
             '<p><a href="edit.php?id=',$cmid,'" >',get_string('edit_blocks', 'ishikawa'),'</a></p>',
             '<p><a href="image.php?id=',$cmid,'&userid=',$USER->id,'&download=1">',get_string('save_image', 'ishikawa'), '</a></p>';
    }

    echo '<img src="image.php?id=',$cmid,'&userid=',$USER->id,'&src=',$src,'&src_type=',$src_type,'&editing=1" usemap="#ishikawamap" />';

    $ishikawa = new Ishikawa($blocks, $connections);
    $rectangles = $ishikawa->retangulos();

    $setas = $ishikawa->setas();

    echo '<map name="ishikawamap">';

    foreach ($setas as $seta) {
        echo '<area shape="rect" coords="',
             $seta->x_delete,',',
             $seta->y_delete,',',
             $seta->x_delete + 10,',',
             $seta->y_delete + 10, '" href="',ishikawa_get_link_to_delete_connection($cmid, $seta->id),'" />';
    }

    foreach ($blocks['causes'] as $nivel_y) {
        foreach ($nivel_y as $b) {
            echo '<area shape="rect" coords="',
                 $rectangles['causes'][$b->id]->upper_x,',',
                 $rectangles['causes'][$b->id]->upper_y,',',
                 $rectangles['causes'][$b->id]->bottom_x,',',
                 $rectangles['causes'][$b->id]->bottom_y, '" href="',ishikawa_get_link_to_block($b, $cmid, $src, 'causes', $dst, 'causes'),'" />';
        }
    }
    foreach ($blocks['axis'] as $b) {
        echo '<area shape="rect" coords="',
             $rectangles['axis'][$b->id]->upper_x,',',
             $rectangles['axis'][$b->id]->upper_y,',',
             $rectangles['axis'][$b->id]->bottom_x,',',
             $rectangles['axis'][$b->id]->bottom_y, '" href="',ishikawa_get_link_to_block($b, $cmid, $src, 'axis', $dst, 'axis'),'" />';
    }
    foreach ($blocks['consequences'] as $nivel_y) {
        foreach ($nivel_y as $b) {
            echo '<area shape="rect" coords="',
                 $rectangles['consequences'][$b->id]->upper_x,',',
                 $rectangles['consequences'][$b->id]->upper_y,',',
                 $rectangles['consequences'][$b->id]->bottom_x,',',
                 $rectangles['consequences'][$b->id]->bottom_y, '" href="',ishikawa_get_link_to_block($b, $cmid, $src, 'consequences', $dst, 'consequences'),'" />';
        }
    }
    echo '</map>';
}

function ishikawa_delete_connection($id) {
    return delete_records('ishikawa_connections', 'id', $id);
}

function ishikawa_view_submission_feedback($ishikawa, $submission, $course) {
    global $USER, $CFG;
    require($CFG->libdir.'/gradelib.php');

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    // Check the user can submit
    $cansubmit = has_capability('mod/ishikawa:submit', $context, $USER->id, false);
    // If not then check if ther user still has the view cap and has a previous submission
    $cansubmit = $cansubmit || (!empty($submission) && has_capability('mod/ishikawa:view', $context, $USER->id, false));

    if (!$cansubmit) {
        // can not submit ishikawas -> no feedback
        return;
    }

    $grading_info = grade_get_grades($course->id, 'mod', 'ishikawa', $ishikawa->id, $USER->id);
    $item = $grading_info->items[0];
    $grade = $item->grades[$USER->id];

    if ($grade->hidden or $grade->grade === false) { // hidden or error
        return;
    }

    if ($grade->grade === null and empty($grade->str_feedback)) {   /// Nothing to show yet
        return;
    }

    $graded_date = $grade->dategraded;
    $graded_by   = $grade->usermodified;

/// We need the teacher info
    if (!$teacher = get_record('user', 'id', $graded_by)) {
        error('Could not find the teacher');
    }

/// Print the feedback
    print_heading(get_string('feedbackfromteacher', 'ishikawa', $course->teacher)); // TODO: fix teacher string

    echo '<table cellspacing="0" class="feedback">';

    echo '<tr>';
    echo '<td class="left picture">';
    if ($teacher) {
        print_user_picture($teacher, $course->id, $teacher->picture);
    }
    echo '</td>';
    echo '<td class="topic">';
    echo '<div class="from">';
    if ($teacher) {
        echo '<div class="fullname">'.fullname($teacher).'</div>';
    }
    echo '<div class="time">'.userdate($graded_date).'</div>';
    echo '</div>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td class="left side">&nbsp;</td>';
    echo '<td class="content">';
    echo '<div class="grade">';
    echo get_string("grade").': '.$grade->str_long_grade;
    echo '</div>';
    echo '<div class="clearer"></div>';

    echo '<div class="comment">';
    echo $grade->str_feedback;
    echo '</div>';
    echo '</tr>';

    echo '</table>';
}

function ishikawa_isopen($ishikawa) {
    $time = time();
    if ($ishikawa->preventlate && $ishikawa->timedue) {
        return ($ishikawa->timeavailable <= $time && $time <= $ishikawa->timedue);
    } else {
        return ($ishikawa->timeavailable <= $time);
    }
}

?>
