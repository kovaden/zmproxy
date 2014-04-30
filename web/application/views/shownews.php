<?php

if (isset($news['header'])) {
    printf('<h3>%s</h3>', $news['header']);
}
if (isset($news['short'])) {
    printf('<p>%s</p>', $news['short']);
}
if (isset($news['full']) && strlen($news['full'])) {
    printf('<div>%s</div>', $news['full']);
}
?>

