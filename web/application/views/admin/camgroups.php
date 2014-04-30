<?php
if (isset($message)) {
    echo '<div class="alert">' . $message . '</div>';
}
?>
<h3>Новая группа камер</h3>
<?php
echo form_open('admin/addgroup');
echo form_input(array(
    'name' => 'name',
    'id' => 'name',
    'value' => (isset($name)) ? $name : '',
    'maxlength' => '255',
    'size' => '50',
    'placeholder' => 'Название',
));
echo form_textarea(array(
    'name' => 'descr',
    'id' => 'descr',
    'value' => (isset($descr)) ? $descr : '',
    'rows' => '2',
    'cols' => '50',
    'placeholder' => 'Описание',
));
echo form_submit('submit', 'Сохранить');
echo form_close();
?>

<h3>Группы камер</h3>
<div class="message">Для редактирования данных пакета камер <i>дважды кликните мышку в поле</i>. Для назначения пакетов камер просто нажмите кнопку.</div>
<?php
echo form_open('admin/campack', array('id' => 'camgroupslist', 'class' => 'tableform'));

echo '<table class="table table-stripped">';
echo "<thead>";
echo "<tr>";
foreach ($fields as $field) {
    echo "<th>$field</th>";
}
echo "<td></td><td>Удаление!!</td></tr></thead><tbody>";
foreach ($list as $k => $v) {
    $id = $v['id'];
    printf('<tr id="tr_%s">', $id);
    $arr_key = array_keys($v);
    foreach (array_keys($v) as $key) {
        if (key_exists($key, $fields)) {
            if ($key != 'id') {
                $editable = 'class="editable"';
            } else {
                $editable = '';
            }
            printf('<td><span %s id="%s_%s">%s</span></td>',$editable, $key, $v['id'], $v[$key]);
        }
    }
    echo '<td><ul>';
    foreach ($packets[$id] as $cam) {
        echo '<li> ' . form_checkbox('cam_' . $cam['id'], 1, $cam['checked']) . " " . $cam['label'] . '</li>';
    }
    echo '</ul>' . form_hidden('gid', $id) . '</td>';
    echo '<td><input type="submit" name="save_' . $id . '" value="сохранить" class="camlist" id="' . $id . '"></td>';
        printf('<td><a class="btn del btn-danger" href="%s">Удалить</a></td>', site_url('admin/delcamgroup/'.$id));

    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo form_close();
?>
