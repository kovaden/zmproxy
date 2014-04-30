<?php
$counter = 0;
foreach ($cam_list as $cam) {

    if ($counter % 3 == 0) {
        echo '<ul class="thumbnails">';
    }
    ?>
    <li class="span4">
        <div class="thumbnail">
            <?php
            if ($cam['down']) {
                printf('<img src="%s/img/down.jpg" alt="Временно выключена">',base_url());
            } else {
                echo anchor('webcam/camera/' . $cam['id'], '<img src="' . $cam_addr . $cam['id'] . '">', array('id' => 'cam' . $cam['id'], 'class' => 'thumb'));
            }
            ?>
            <div class="caption">
                <h5><?php echo $cam['name']; ?></h5>
                <p><?php echo $cam['descr']; ?></p>
            </div>
        </div>
    </li>
    <?php
    $counter += 1;

    if ($counter % 3 == 0) {
        echo '</ul>';
    }
}
if ($counter % 3 != 0) {
    echo '</ul>';
}
?>

