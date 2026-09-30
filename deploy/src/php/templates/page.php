<html lang="<?= e($site["language"]) ?>">

<!--
    There is intentionally no doctype, the website has always been rendered in quirks mode.
    In standards mode, every image in a line of text gets a few pixels of extra space below it.
-->


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="language" content="<?= e($site["language"]) ?>">

    <title><?= e($title) ?></title>

<?php if ($description !== ""): ?>
    <meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
    <meta property="og:site_name" content="<?= e($site["title"]) ?>">
    <meta property="og:title" content="<?= e($project === null ? $site["title"] : $project["name"]) ?>">
    <meta property="og:type" content="<?= $project === null ? "website" : "article" ?>">
<?php if ($description !== ""): ?>
    <meta property="og:description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($site["url"] !== ""): ?>
    <meta property="og:url" content="<?= e($url) ?>">
    <link rel="canonical" href="<?= e($url) ?>">
<?php endif; ?>
<?php if ($image !== null and $site["url"] !== ""): ?>
    <meta property="og:image" content="<?= e($image) ?>">
    <meta name="twitter:card" content="summary_large_image">
<?php else: ?>
    <meta name="twitter:card" content="summary">
<?php endif; ?>

    <link rel="shortcut icon" type="image/x-icon" href="/src/img/favicon.ico" />

    <link rel="stylesheet" href="/src/css/github-markdown.css">
    <link rel="stylesheet" href="/src/css/custom-main.css">

    <style>
        .markdown-body,
        .popup-header-content {
            min-width: <?= e($site["min_width"]) ?>;
            max-width: <?= e($site["max_width"]) ?>;
        }
    </style>

    <script type="module" src="/src/js/core.js"></script>
</head>

<body data-page="<?= e($page_id) ?>" data-title="<?= e($site["title"]) ?>">
    <div id="landing" style="<?= $project === null ? "display: block;" : "display: none;" ?>">
<?= $landing_html ?>
    </div>

    <div id="popup" class="markdown-body" style="<?= $project === null ? "display: none;" : "display: block; min-height: 100%;" ?>">
        <div class="fullpanel">
            <div class="popup-header">
                <div class="popup-header-content">
                    <div class="popup-header-title">
                        <h1 id="project-title" class="mt-3 ml-3 border-none"><?= $project === null ? "" : e($project["name"]) ?></h1>
                    </div>

                    <a title="Close" id="button-close" class="popup-header-button" href="/"></a>

                </div>
            </div>

            <div class="markdown-body popup-body">
<?php if ($project !== null and $project_info !== ""): ?>
                <div id="project-top" class="project-info-box"><?= $project_info ?></div>
<?php endif; ?>
                <div id="project-text">
<?= $project === null ? "" : $project_html ?>
                </div>
<?php if ($project !== null and $project_topics !== ""): ?>
                <div id="project-footer" class="project-info-box"><b>Topics: </b><?= $project_topics ?></div>
<?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
