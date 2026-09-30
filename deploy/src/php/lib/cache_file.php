<?php

function cache_file($project, $config) {
    echolog("started caching " . $project["id"], 2);

    if ($project["repo_based"]) {
        echolog("project is repository based", 3);

        $readme_path = $config["raw_base"]
            . $project["owner"] . "/"
            . $project["id"] . "/"
            . $project["default_branch"] . "/README.md";
    } else {
        echolog("project is only a markdown file", 3);

        $readme_path = $config["raw_base"]
            . $config["core"]["owner"] . "/"
            . $config["core"]["repository"] . "/"
            . $config["core"]["default_branch"] . "/webcontent/markdown/"
            . $project["id"] . ".md";
    }

    $markdown = request_get_file_contents($readme_path);

    echolog("fetched file from server", 2);

    echolog("requesting translated markdown from GitHub server via API call", 2);

    // store file as cache
    if ($project["repo_based"]) {
        $html_path = "../cache/"
            . $project["owner"] . "/"
            . $project["id"] . "/"
            . $project["default_branch"];
        $html_file = $html_path . "/README.html";

        $response = request_markdown($config["api_key"], $markdown, "gfm", $project["owner"] . "/" . $project["id"]);
    } else {
        $html_path = "../cache/"
            . $config["core"]["owner"] . "/"
            . $config["core"]["repository"] . "/"
            . $config["core"]["default_branch"];
        $html_file = $html_path . "/" . $project["id"] . ".html";

        $response = request_markdown($config["api_key"], $markdown, "gfm", $config["core"]["owner"] . "/" . $project["id"]);
    }

    $html = $response["result"];

    echolog("received translated markdown file from server", 2);

    // links are fixed in the rendered html, this way code blocks are skipped automatically
    // because their content is text and not a link element
    if ($response["status"] == 200) {
        $html = fix_links($html, $project, $config);
    }

    if (!is_dir($html_path)) {
        mkdir($html_path, 0777, true);
    }
    file_put_contents($html_file, $html);

    echolog("stored file in cache on server", 2);
}

function fix_links($html, $project, $config) {
    echolog("starting link fixing", 2);

    // wrap the fragment in a full document so that DOMDocument reads it as UTF-8
    $dom = new DOMDocument();

    $use_errors = libxml_use_internal_errors(true); // unknown html5 tags would raise warnings
    $dom->loadHTML("<!DOCTYPE html><html><head><meta charset=\"utf-8\"></head><body>" . $html . "</body></html>");
    libxml_clear_errors();
    libxml_use_internal_errors($use_errors);

    // collect all links first, GitHub wraps images in a link to the image itself,
    // those links should point to the raw image as well
    $found_links = [];

    foreach ($dom->getElementsByTagName("a") as $element) {
        $is_image = false;

        foreach ($element->getElementsByTagName("img") as $img) {
            if ($img->getAttribute("src") === $element->getAttribute("href")) {
                $is_image = true;

                break;
            }
        }

        array_push($found_links, [$element, "href", $is_image]);
    }

    foreach ($dom->getElementsByTagName("img") as $element) {
        array_push($found_links, [$element, "src", true]);
    }

    echolog("found " . count($found_links) . " links in document, some may need fixing", 2);

    foreach ($found_links as $i => [$element, $attribute, $is_image]) {
        $link = $element->getAttribute($attribute);

        echolog($i . ". " . $link, 3);

        // ignore empty links and anchors inside of the document
        if ($link === "" or str_starts_with($link, "#")) {
            echolog("link is an anchor that doesn't need fixing, continuing", 4);

            continue;
        }

        // ignore links that point to external sources or use a scheme such as mailto:
        if (str_starts_with($link, "//") or preg_match("/^[a-z][a-z0-9+.-]*:/i", $link)) {
            echolog("link is absolute link that doesn't need fixing, continuing", 4);

            continue;
        }

        $new_link = build_link($link, $is_image, $project, $config);

        echolog("link is fixed: " . $new_link, 4);

        $element->setAttribute($attribute, $new_link);
    }

    // only return the content of the body, not the wrapper document
    $fixed_html = "";

    foreach ($dom->getElementsByTagName("body")->item(0)->childNodes as $node) {
        $fixed_html .= $dom->saveHTML($node);
    }

    return $fixed_html;
}

function build_link($link, $is_image, $project, $config) {
    // we want to use the raw file for images, the link to the repo for normal links
    if ($is_image) {
        $base = $config["raw_base"];
    } else {
        $base = $config["file_base"];
    }

    if ($project["repo_based"]) {
        return $base
            . $project["owner"] . "/"
            . $project["id"] . "/"
            . ($is_image ? "" : "blob/" )
            . $project["default_branch"] . "/"
            . $link;
    }

    return $base
        . $config["core"]["owner"] . "/"
        . $config["core"]["repository"] . "/"
        . ($is_image ? "" : "blob/")
        . $config["core"]["default_branch"]
        . "/webcontent/assets/"
        . $link;
}

?>