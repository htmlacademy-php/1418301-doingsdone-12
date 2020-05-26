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
                        <a href="/?<?php if ($current_project_id): ?>project_id=<?=$current_project_id?>&<?php endif; ?>filter=1" class="tasks-switch__item<?php if ($filter === '1' || !$filter): ?> tasks-switch__item--active<?php endif; ?>">Все задачи</a>
                        <a href="/?<?php if ($current_project_id): ?>project_id=<?=$current_project_id?>&<?php endif; ?>filter=2" class="tasks-switch__item<?php if ($filter === '2'): ?> tasks-switch__item--active<?php endif; ?>">Повестка дня</a>
                        <a href="/?<?php if ($current_project_id): ?>project_id=<?=$current_project_id?>&<?php endif; ?>filter=3" class="tasks-switch__item<?php if ($filter === '3'): ?> tasks-switch__item--active<?php endif; ?>">Завтра</a>
                        <a href="/?<?php if ($current_project_id): ?>project_id=<?=$current_project_id?>&<?php endif; ?>filter=4" class="tasks-switch__item<?php if ($filter === '4'): ?> tasks-switch__item--active<?php endif; ?>">Просроченные</a>
                    </nav>

                    <label class="checkbox">
                        <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
                        <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks == 1): ?>checked<?php endif; ?>>
                        <span class="checkbox__text">Показывать выполненные</span>
                    </label>
                </div>

                <?php if (count($task_rows)): ?>
                <table class="tasks">
                    <?php
                        foreach ($task_rows as $val):
                        if ($show_complete_tasks === 0 && (int)$val['status'] === 1) continue;
                    ?>
                    <tr class="tasks__item task<?php if ((int)$val['status'] === 1): ?> task--completed<?php endif; if (!task_date_ckeck($val['date_execute'])): ?> task--important<?php endif; ?>">
                        <td class="task__select">
                            <label class="checkbox task__checkbox">
                                <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" onclick="window.location = '/?set_task_execute=<?=$val['id']?>&status=<?=$val['status']?>'">
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
                <?php elseif (getGetVal('query')): ?>
                <p>Ничего не найдено по вашему запросу</p>
                <?php endif; ?>
        </main>