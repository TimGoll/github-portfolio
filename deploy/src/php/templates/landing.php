        <div id="bio" class="markdown-body mb-3 mt-3">
<?= $bio_html ?>
        </div>

        <div class="markdown-body mb-3">
            <div>
                <h1 dir="auto" class="mt-0">
                    My Projects
                </h1>

                <div id="projects" class="d-flex flex-wrap gutter-condensed mb-2 pl-0">
<?php foreach ($project_list as $project): ?>
<?php if (!empty($project["hidden"])) continue; ?>
<?php $preview = $project["preview"]; $date = format_date($project["date"]); ?>
                    <div class="mb-3 d-flex flex-content-stretch col-12 col-md-6 col-lg-4">
                        <div class="Box of-hidden d-flex w-100 project-list-item-item">
                            <a class="project-list-item-content" href="/<?= e($project["id"]) ?>" data-project="<?= e($project["id"]) ?>">
                                <div class="image-box-height of-hidden img-project">
                                    <img class="object-fit-cover w-100 h-100" src="<?= e($preview["url"]) ?>"<?php if ($preview["width"] !== null): ?> width="<?= $preview["width"] ?>" height="<?= $preview["height"] ?>"<?php endif; ?> loading="lazy" alt="">
<?php if ($date !== ""): ?>
                                    <div class="date-box"><?= $date ?></div>
<?php endif; ?>
                                </div>
                                <div class="d-flex flex-dir-col flex-grow-2 p-3">
                                    <h3 class="mt-0"><?= e($project["name"]) ?></h3>
                                    <p class="mb-0"><?= e($project["description"]) ?></p>
                                </div>
                                <div class="d-flex flex-grow-1 p-3 pt-0 topics-area">
<?php foreach ($project["topics"] as $topic): ?>
<?php $color = topic_color($topic); ?>
                                    <div class="topic-box" style="background-color: <?= $color ?>; color: <?= topic_text_color($color) ?>"><?= e($topic) ?></div>
<?php endforeach; ?>
                                </div>
                            </a>
                        </div>
                    </div>
<?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="footer" class="markdown-body mb-3">
            <a href="/legal"<?php if ($has_legal): ?> data-project="legal"<?php endif; ?>>Legal Notice</a> - <a href="mailto:<?= e($site["contact"]) ?>">Contact</a> - <a href="https://github.com/<?= e($core["owner"]) ?>/<?= e($core["repository"]) ?>" target="_blank">Source</a> - © <?= e($site["copyright"]["start_year"]) ?>-<?= date("Y") ?> by <?= e($site["copyright"]["name"]) ?>
        </div>
