<section class="content__side">
        <p class="content__side-info">Если у вас уже есть аккаунт, авторизуйтесь на сайте</p>

        <a class="button button--transparent content__side-button" href="/auth.php">Войти</a>
      </section>

      <main class="content__main">

        <h2 class="content__main-heading">Вход на сайт</h2>

        <form class="form" action="" method="post" autocomplete="off">
          <div class="form__row">
            <label class="form__label" for="email">E-mail <sup>*</sup></label>

            <input class="form__input<?php if (isset($errors['auth_email'])): ?> form__input--error<?php endif; ?>" type="text" name="email" id="email" value="<?=getPostVal('email')?>" placeholder="Введите e-mail">

            <?php if (isset($errors['auth_email'])): ?><p class="form__message"><?=$errors['auth_email']?></p><?php endif; ?>
          </div>

          <div class="form__row">
            <label class="form__label" for="password">Пароль <sup>*</sup></label>

            <input class="form__input<?php if (isset($errors['auth_password'])): ?> form__input--error<?php endif; ?>" type="password" name="password" id="password" value="" placeholder="Введите пароль">

            <?php if (isset($errors['auth_password'])): ?><p class="form__message"><?=$errors['auth_password']?></p><?php endif; ?>
          </div>

          <div class="form__row form__row--controls">
            <?php if (isset($errors['auth'])): ?><p class="error-message"><?=$errors['auth']?></p><?php endif; ?>

            <input class="button" type="submit" name="auth" value="Войти">
          </div>
        </form>

      </main>