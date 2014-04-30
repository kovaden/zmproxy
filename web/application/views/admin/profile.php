<?php

//print_r ($fields);
foreach ($user as $k=>$v) {
    if (in_array($k, array_keys($fields))) {
        if (strlen($fields[$k])) {
            echo " <br> " . $fields[$k] . " " . $v;
        } else {
            echo  " ". $v . " ";
        }
    }
}
?>
