<h2 class="content__side-heading">Проекты</h2>

                <nav class="main-navigation">
                    <ul class="main-navigation__list">
                        <?php foreach ($project_rows as $val): ?>
                        <li class="main-navigation__list-item<?php if (current_project_check($val['id'], $current_project_id)): ?> main-navigation__list-item--active<?php endif; ?>">
                            <a class="main-navigation__list-item-link" href="index.php?prj_id=<?=$val['id']?>"><?=$val['title']?></a>
                            <span class="main-navigation__list-item-count"><?=$val['task_count']?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <a class="button button--transparent button--plus content__side-button"
                   href="pages/form-project.html" target="project_add">Добавить проект</a>