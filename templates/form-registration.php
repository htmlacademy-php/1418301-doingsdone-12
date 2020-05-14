<h2 class="content__main-heading">Регистрация аккаунта</h2>

          <form class="form" action="" method="post" autocomplete="off">
            <div class="form__row">
              <label class="form__label" for="email">E-mail <sup>*</sup></label>

              <input class="form__input<?php if (isset($errors['reg_email'])): ?> form__input--error<?php endif; ?>" type="text" name="email" id="email" value="<?=getPostVal('email')?>" placeholder="Введите e-mail">

              <?php if (isset($errors['reg_email'])): ?><p class="form__message"><?=$errors['reg_email']?></p><?php endif; ?>
            </div>

            <div class="form__row">
              <label class="form__label" for="password">Пароль <sup>*</sup></label>

              <input class="form__input<?php if (isset($errors['reg_password'])): ?> form__input--error<?php endif; ?>" type="password" name="password" id="password" value="<?=getPostVal('password')?>" placeholder="Введите пароль">

              <?php if (isset($errors['reg_password'])): ?><p class="form__message"><?=$errors['reg_password']?></p><?php endif; ?>
            </div>

            <div class="form__row">
              <label class="form__label" for="name">Имя <sup>*</sup></label>

              <input class="form__input<?php if (isset($errors['reg_name'])): ?> form__input--error<?php endif; ?>" type="text" name="name" id="name" value="<?=getPostVal('name')?>" placeholder="Введите имя">

              <?php if (isset($errors['reg_name'])): ?><p class="form__message"><?=$errors['reg_name']?></p><?php endif; ?>
            </div>

            <div class="form__row form__row--controls">
              <?php if (count($errors) > 0 ): ?><p class="error-message">Пожалуйста, исправьте ошибки в форме</p><?php endif; ?>

              <input class="button" type="submit" name="registration" value="Зарегистрироваться">
            </div>
          </form>