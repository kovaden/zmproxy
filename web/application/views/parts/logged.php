<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    <i class="icon-user"></i> <?php echo $fio; ?>
    <span class="caret"></span>
</a>
<ul class="dropdown-menu">
    <li><a href="#">Профиль</a></li>
    <li class="divider"></li>
    <li><?php echo anchor('logout','Выйти'); ?></li>
</ul>