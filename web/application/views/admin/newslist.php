<?php
if (isset($message)) {
    echo '<div class="alert">' . $message . '</div>';
}

echo '<h3>Новая новость</h3>';
echo form_open('admin/addnews');
echo form_input(array(
    'name' => 'header',
    'id' => 'header',
    'value' => (isset($header)) ? $header : '',
    'maxlength' => '255',
    'size' => '50',
    'placeholder' => 'Заголовок',
));
echo form_textarea(array(
    'name' => 'short',
    'id' => 'short',
    'value' => (isset($short)) ? $short : '',
    'rows' => '5',
    'cols' => '50',
    'placeholder' => 'Кратко',
));
echo form_textarea(array(
    'name' => 'full',
    'id' => 'full',
    'value' => (isset($full)) ? $full : '',
    'rows' => '5',
    'cols' => '100',
    'style' => 'width:500px;',
    'placeholder' => 'Максимально подробно',
));
echo form_submit('submit', 'Сохранить');
echo form_close();
?>


<h3>Старые новости</h3>
<div class="message">Для редактирования новости <i>дважды кликните мышку в поле</i></div>
<?php
echo form_open('admin/delnews');

echo '<table class="table table-stripped tableform" id="newslist">';
echo "<thead>";
echo "<tr>";
foreach ($fields as $field) {
    echo "<th>$field</th>";
}
echo "<td></td>";
echo "</tr></thead><tbody>";
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
                $editable = 'class="editable-area"';
            } else {
                $editable = '';
            }
            printf('<td %s id="%s_%s">%s</td>',$editable, $key, $v['id'], $v[$key]);
        }
    }

    echo '<td>' . form_hidden('id', $id) . '<input type="submit" class="del" value="удалить" class="newslist" id="' . $id . '"></td>';
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo form_close();
?>
