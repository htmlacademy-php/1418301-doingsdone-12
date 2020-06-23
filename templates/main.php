<section class="content__side">
                <?=$menu?>
            </section>
            
            <main class="content__main">
            <h2 class="content__main-heading">Список задач</h2>

                <form class="search-form" action="" method="get" autocomplete="off">
                    <input class="search-form__input" type="text" name="query" value="<?=getGetVal('query')?>" placeholder="Поиск по задачам">

                    <input class="search-form__submit" type="submit" name="" value="Искать">
                </form>

                <div class="tasks-controls">
                    <nav class="tasks-switch">
                        <a href="<?=get_filter_request($current_project_id, 1)?>" class="tasks-switch__item<?php if ((string)$filter === '1' || !$filter): ?> tasks-switch__item--active<?php endif; ?>">Все задачи</a>
                        <a href="<?=get_filter_request($current_project_id, 2)?>" class="tasks-switch__item<?php if ((string)$filter === '2'): ?> tasks-switch__item--active<?php endif; ?>">Повестка дня</a>
                        <a href="<?=get_filter_request($current_project_id, 3)?>" class="tasks-switch__item<?php if ((string)$filter === '3'): ?> tasks-switch__item--active<?php endif; ?>">Завтра</a>
                        <a href="<?=get_filter_request($current_project_id, 4)?>" class="tasks-switch__item<?php if ((string)$filter === '4'): ?> tasks-switch__item--active<?php endif; ?>">Просроченные</a>
                    </nav>

                    <label class="checkbox">
                        <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
                        <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ((int)$show_complete_tasks === 1): ?>checked<?php endif; ?>>
                        <span class="checkbox__text">Показывать выполненные</span>
                    </label>
                </div>

                <?php if (count($task_rows)): ?>
                <table class="tasks">
                    <?php
                        foreach ($task_rows as $val):
                        if (isset($val['status']) && (int)$show_complete_tasks === 0 && (int)$val['status'] === 1) {
                            continue;
                        }
                    ?>
                    <tr class="tasks__item task<?php if (isset($val['status']) && (int)$val['status'] === 1): ?> task--completed<?php endif; if (isset($val['date_execute']) && !task_date_ckeck($val['date_execute'])): ?> task--important<?php endif; ?>">
                        <td class="task__select">
                            <label class="checkbox task__checkbox">
                                <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" onclick="window.location = '/?set_task_execute=<?=getVal($val, 'id')?>&status=<?=getVal($val, 'status')?>'">
                                <span class="checkbox__text"><?=getVal($val, 'title')?></span>
                            </label>
                        </td>

                        <td class="task__file">
                            <?php if (isset($val['file']) && $val['file'] !== NULL): ?><a class="download-link" href="<?=strip_tags($val['file'])?>"><?=str_replace('/uploads/', '', strip_tags($val['file']))?></a><?php endif; ?>
                        </td>

                        <td class="task__date"><?php if (isset($val['date_execute']) && $val['date_execute'] !== NULL) print(date("d.m.Y", strtotime(strip_tags($val['date_execute'])))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php elseif (getGetVal('query')): ?>
                <p>Ничего не найдено по вашему запросу</p>
                <?php endif; ?>
        </main>