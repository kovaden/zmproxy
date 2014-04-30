<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        Админка
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
        <li><?php echo anchor('admin','Список пользователей');?></li>
        <li><?php echo anchor('admin/newuser','Новый пользователь');?></li>
        <li><?php echo anchor('admin/camgroups','Группы камер');?></li>
        <li><?php echo anchor('admin/camlist','Список камер');?></li>
        <li><?php echo anchor('admin/newslist','Новости');?></li>
    </ul>
</li>