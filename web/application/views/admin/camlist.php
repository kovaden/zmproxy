<?php
if (isset($message)) {
    echo '<div class="alert">' . $message . '</div>';
}
?>
<h3>Новая камера</h3>
<div class="row-fluid">

    <?php
    echo form_open('admin/addcam');
    echo '<div class="span1"></div>';
    echo '<div class="span3">';
    echo form_input(array(
        'name' => 'name',
        'id' => 'name',
        'value' => (isset($name)) ? $name : '',
        'maxlength' => '255',
        'size' => '50',
        'placeholder' => 'Название',
    ));
    echo form_input(array(
        'name' => 'user',
        'id' => 'user',
        'value' => (isset($user)) ? $user : '',
        'maxlength' => '255',
        'size' => '50',
        'placeholder' => 'zm user',
    ));
    echo form_input(array(
        'name' => 'pass',
        'id' => 'pass',
        'value' => (isset($pass)) ? $pass : '',
        'maxlength' => '255',
        'size' => '50',
        'placeholder' => 'zm pass',
    ));
    echo '</div>';
    echo '<div class="span3">';

    echo form_textarea(array(
        'name' => 'descr',
        'id' => 'descr',
        'value' => (isset($descr)) ? $descr : '',
        'rows' => '5',
        'cols' => '50',
        'placeholder' => 'Описание',
    ));
    echo '</div>';
    echo '<div class="span3">';
    echo form_textarea(array(
        'name' => 'url',
        'id' => 'url',
        'value' => (isset($url)) ? $url : '',
        'rows' => '3',
        'cols' => '50',
        'placeholder' => 'Адрес',
    ));

    echo form_submit('submit', 'Сохранить');
    echo '</div>';
    echo '<div class="span2"></div>';
    echo form_close();
    ?>
</div>
<h3>Список камер</h3>
<div class="message">Для редактирования данных камеры <i>дважды кликните мышку в поле</i></div>
<?php
echo form_open('admin/campack', array('id' => 'camlist', 'class' => 'tableform'));

echo '<table class="table table-stripped">';
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
            switch ($key) {
                case 'id' :
                    printf('<td id="%s_%s">%s</td>', $key, $v['id'], $v[$key]);
                    break;
                case 'down':
                    printf('<td class="editable-checkbox" id="%s_%s">%s</td>', $key, $v['id'], $v[$key]);
                    break;
                default:
                    printf('<td class="editable" id="%s_%s">%s</td>', $key, $v['id'], $v[$key]);
                    break;
            }

            
        }
    }

//    echo '<td>'.form_hidden('uid', $id) .'<input type="submit" name="save_' . $id . '" value="сохранить" class="camlist" id="' . $id . '"></td>';
    printf('<td><a class="btn del btn-danger" href="%s">Удалить</a></td>', site_url('admin/delcam/'.$id));
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo form_close();
?>
