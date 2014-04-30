<?php
if (isset($message)) {
    echo '<div class="alert">' . $message . '</div>';
}
?>
<div class="message">Для редактирования данных пользователя <i>дважды кликните мышку в поле</i>. Для назначения пакетов камер просто нажмите кнопку.</div>
<?php
echo form_open('admin/packets', array('id' => 'userlist', 'class' => 'tableform'));

echo '<table class="table table-stripped">';
echo "<thead>";
echo "<tr>";
foreach ($fields as $field) {
    echo "<th>$field</th>";
}
echo "<td></td><td>Удаление!!</td></tr></thead><tbody>";
foreach ($list as $k => $v) {
    $id = $v['id'];
    if (isset($new_id) && $id == $new_id) {
        $tr_active = ' class="active" ';
    } else {
        $tr_active = '';
    }
    printf('<tr id="tr_%s" %s>', $id, $tr_active);
    $arr_key = array_keys($v);
    foreach (array_keys($v) as $key) {
        if (key_exists($key, $fields)) {
            if ($key != 'id') {
                $editable = 'class="editable"';
            } else {
                $editable = '';
            }
            printf('<td><span %s id="%s_%s">%s</span></td>', $editable, $key, $v['id'], $v[$key]);
        }
    }
    echo '<td><ul>';
    foreach ($packets[$id] as $cam) {
        echo '<li> ' . form_checkbox('cam_' . $cam['id'], 1, $cam['checked']) . " " . $cam['label'] . '</li>';
    }
    echo '</ul>' . form_hidden('uid', $id) . '</td>';
    echo '<td><input type="submit" name="save_' . $id . '" value="сохранить" class="camlist" id="' . $id . '"></td>';
    printf('<td><a class="btn del btn-danger" href="%s">Удалить</a></td>', site_url('admin/deluser/' . $id));

    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo form_close();
?>
