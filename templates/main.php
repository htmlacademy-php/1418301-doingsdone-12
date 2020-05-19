<section class="content__side">
                <?=$menu?>
            </section>
            
            <main class="content__main">
            <h2 class="content__main-heading">Список задач</h2>

                <form class="search-form" action="index.php" method="post" autocomplete="off">
                    <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

                    <input class="search-form__submit" type="submit" name="" value="Искать">
                </form>

                <div class="tasks-controls">
                    <nav class="tasks-switch">
                        <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
                        <a href="/" class="tasks-switch__item">Повестка дня</a>
                        <a href="/" class="tasks-switch__item">Завтра</a>
                        <a href="/" class="tasks-switch__item">Просроченные</a>
                    </nav>

                    <label class="checkbox">
                        <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
                        <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks == 1): ?>checked<?php endif; ?>>
                        <span class="checkbox__text">Показывать выполненные</span>
                    </label>
                </div>

                <table class="tasks">
                    <?php
                        foreach ($task_rows as $val):
                        if ($show_complete_tasks === 0 && (int)$val['status'] === 1) continue;
                    ?>
                    <tr class="tasks__item task<?php if ((int)$val['status'] === 1): ?> task--completed<?php endif; if (!task_date_ckeck($val['date_execute'])): ?> task--important<?php endif; ?>">
                        <td class="task__select">
                            <label class="checkbox task__checkbox">
                                <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1">
                                <span class="checkbox__text"><?=htmlspecialchars($val['title'])?></span>
                            </label>
                        </td>

                        <td class="task__file">
                            <?php if ($val['file'] != null): ?><a class="download-link" href="<?=$val['file']?>"><?=str_replace('/uploads/', '', $val['file'])?></a><?php endif; ?>
                        </td>

                        <td class="task__date"><?php if($val['date_execute'] != null) print(date("d.m.Y",strtotime($val['date_execute']))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
        </main>