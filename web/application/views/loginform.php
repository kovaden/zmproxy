<?php
if (isset($error) && $error) {
    $this->load->view('parts/error');
}
?>


<?php echo form_open('login', array('id' => "loginForm", "class" => "well")) ?>
<fieldset id="body">
    <fieldset>
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" id="username" class="medium" />
    </fieldset>
    <fieldset>
        <label for="password">Пароль</label>
        <input type="password" name="password" id="password" class="medium" />
    </fieldset>
    <fieldset class="form-inline">
        <input type="submit" id="login" value="Войти" class="btn btn-primary" />
        <label for="checkbox" class="checkbox"><input type="checkbox" id="checkbox" />Запомнить меня</label>
    </fieldset>
</fieldset>
<div class="form-inline">
    <span><a href="#">Забыли пароль?</a></span>
    <span><a href="<?php echo base_url().index_page(); ?>/registration">Зарегистрироваться</a></span>
</div>
</form>
