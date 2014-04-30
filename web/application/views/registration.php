<div class="page-header">
    <h1><?php echo $title; ?></h1>
</div>
<?php //print_r ($user); print_r ($error); ?>
<?php echo form_open($handler, array('id' => "registration", "class" => "well")) ?>
<fieldset>
    <legend>
        Для получения доступа ко всей информации на сайте зарегистрируйтесь.    <br>
        <small>Все поля должны быть заполнены.</small>
    </legend>
    <?php if (isset($error['all']) && $error['all']) : ?>
        <div class="alert alert-error"><?php echo $error['all']; ?></div>
    <?php endif ?>

    <div class="clearfix">
        <label for="username">Ваше имя</label>
        <input class="input-xlarge" minlength="2" id="name" name="name" required placeholder="Ваше имя..." size="30" <?php if (isset($user['name'])) echo 'value="' . $user['name'] . '"'; ?>>
        <?php if (isset($error['name'])) : ?>
            <span class="alert-error"><?php echo $error['name']; ?></span>
        <?php endif ?>
    </div>

    <div class="clearfix">
        <label for="username">Фамилия</label>
        <input class="input-xlarge" minlength="2" id="lastname" name="lastname" required placeholder="...и фамилия" size="30" <?php if (isset($user['lastname'])) echo 'value="' . $user['lastname'] . '"'; ?>>
        <?php if (isset($error['lastname'])) : ?>
            <span class="alert-error"><?php echo $error['lastname']; ?></span>
        <?php endif ?>
    </div>

    <div class="clearfix">
        <label for="username">Имя пользователя</label>
        <input class="input-xlarge required" minlength="2" id="username" name="username" required placeholder="Выберите себе псевдоним" size="30" pattern="[a-zA-Z0-9]{3,}" <?php if (isset($user['username'])) echo 'value="' . $user['username'] . '"'; ?>>
        <?php if (isset($error['username'])) : ?>
            <span class="alert-error"><?php echo $error['username']; ?></span>
        <?php endif ?>
        <span class="help-block">Латинские буквы и цифры, и без пробелов, пожалуйста. И не меньше 3 символов!</span>
    </div>

    <div class="clearfix">
        <label for="email">Email</label>
        <input class="input-xlarge required email" id="email" name="email" type="email" required placeholder="Нам нужно знать ваш почтовый адрес" size="30"<?php if (isset($user['email'])) echo 'value="' . $user['email'] . '"'; ?>>
        <?php if (isset($error['email'])) : ?>
            <span class="alert-error"><?php echo $error['email']; ?></span>
        <?php endif ?>
        <span class="help-block">Все спрашивают, и нам пригодится. Может быть, вышлем информацию об обновлениях на сайте, а может спамерам продадим.</span>
    </div>

    <div class="clearfix">
        <label for="email">Если вдруг вы хотите что-то добавить, пишите это здесь.</label>
        <textarea class="input-xlarge" id="info" name="info" size="30"><?php if (isset($user['info'])) echo $user['info']; ?></textarea>
        <?php if (isset($error['info'])) : ?>
            <span class="alert-error"><?php echo $error['info']; ?></span>
        <?php endif ?>
        <span class="help-block"> (чтоб можно было добавить какую=то свободную инфу (типа телефон, адрес и тп)</span>
    </div>

    <div class="clearfix">
        <label for="password">Пароль</label>
        <input class="input-xlarge required" id="password" name="password" type="password" required placeholder="Придумайте себе секретный пароль" size="30">
        <span class="help-block">Пароль тоже нужен, мы потом проверим. Да, и 5 символов, не меньше! Зато любые.</span>
    </div>

    <div class="clearfix">
        <label for="confirm_password">Подтверждение пароля</label>
        <input class="input-xlarge required" id="confirm_password" name="confirm_password" type="password" required placeholder="Ещё разок" size="30">
        <?php if (isset($error['confirm_password'])) : ?>
            <span class="alert-error"><?php echo $error['confirm_password']; ?></span>
        <?php endif ?>
        <span class="help-block">Мы его проверим дважды!</span>
    </div>

    <div class="actions">
        <input class="btn btn-primary" type="submit" value="<?php echo $title; ?>">
    </div>
</fieldset>

</form>
