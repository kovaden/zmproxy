<h2>Все камеры Истры</h2>

<div id="myCarousel" class="carousel slide vertical">
    <!-- Carousel items -->
    <div class="carousel-inner">
        <?php
        $img = '-prev'; // patch for camera single frame
        foreach ($cams as $cam) {
            printf('<div class="item"><a href="%s"><img src="%s/img/thumbnails/cam%s.jpg" /><div class="carousel-caption"><h4>%s</h4><p>%s</p></div></a></div>%s', site_url('webcam/camera/') . '/' . $cam['id'], base_url(), $img, $cam['name'], $cam['descr'], "\n");
        }
        ?>
    </div>
    <!-- Carousel nav -->
    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&DoubleUpArrow;</a>
    <a class="carousel-control right" href="#myCarousel" data-slide="next">&DoubleDownArrow;</a>

</div>