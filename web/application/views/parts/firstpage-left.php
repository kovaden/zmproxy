<?php 

foreach ($news_list as $news) {
    if (isset($news['header'])) {
        printf('<h2>%s</h2>',$news['header']);
    }
    if (isset($news['short'])) {
        printf('<p>%s</p>',$news['short']);
    }
    if (isset($news['full']) && strlen($news['full'])) {
        printf('<p>%s</p>',
                anchor('news/show/'.$news['id'],'Читать дальше &raquo;','class="btn info"  data-target="#myModal"'));
    }
    echo '<hr>';
}
?>
