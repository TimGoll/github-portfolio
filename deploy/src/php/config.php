<?php
    $config = [
        "raw_base" => "https://raw.githubusercontent.com/",
        "file_base" => "https://github.com/",
        "core" => [
            "owner" => "TimGoll",
            "repository" => "TimGollDE",
            "default_branch" => "main"
        ],
        "bio" => [
            "owner" => "TimGoll",
            "repository" => "TimGoll",
            "default_branch" => "main"
        ],
        "api_key" => "xxx"
    ];

    // secrets.php is not tracked by git and overwrites values such as the api key
    if (file_exists(__DIR__ . "/secrets.php")) {
        $config = array_replace_recursive($config, include(__DIR__ . "/secrets.php"));
    }
?>