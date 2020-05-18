<section class="content__side">
      <?=$menu?>
    </section>
            
    <main class="content__main">
      <h2 class="content__main-heading">Добавление задачи</h2>

        <form class="form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
          <div class="form__row">
            <label class="form__label" for="name">Название <sup>*</sup></label>

            <input class="form__input<?php if (isset($errors['task_title'])): ?> form__input--error<?php endif; ?>" type="text" name="name" id="name" value="<?=getPostVal('name')?>" placeholder="Введите название">

            <?php if (isset($errors['task_title'])): ?><p class="form__message"><?=$errors['task_title']?></p><?php endif; ?>
          </div>

          <div class="form__row">
            <label class="form__label" for="project">Проект <sup>*</sup></label>

            <select class="form__input form__input--select<?php if (isset($errors['task_project_id'])): ?> form__input--error<?php endif; ?>" name="project" id="project">
              <?php foreach ($project_rows as $val): ?>
              <option value="<?=$val['id']?>"><?=$val['title']?></option>
              <?php endforeach; ?>
            </select>

            <?php if (isset($errors['task_project_id'])): ?><p class="form__message"><?=$errors['task_project_id']?></p><?php endif; ?>
          </div>

          <div class="form__row">
            <label class="form__label" for="date">Дата выполнения</label>

            <input class="form__input form__input--date<?php if (isset($errors['task_date'])): ?> form__input--error<?php endif; ?>" type="text" name="date" id="date" value="<?=getPostVal('date')?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">

            <?php if (isset($errors['task_date'])): ?><p class="form__message"><?=$errors['task_date']?></p><?php endif; ?>
          </div>

          <div class="form__row">
            <label class="form__label" for="file">Файл</label>

            <div class="form__input-file">
              <input class="visually-hidden" type="file" name="file" id="file" value="">

              <label class="button button--transparent" for="file">
                <span>Выберите файл</span>
              </label>
            </div>
          </div>

          <div class="form__row form__row--controls">
            <?php if (count($errors) > 0 ): ?><p class="error-message">Пожалуйста, исправьте ошибки в форме</p><?php endif; ?>
            
            <input class="button" type="submit" name="add" value="Добавить">
          </div>
        </form>
      </main>