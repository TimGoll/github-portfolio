<?php

const PAGE_FOLDER = "pages";

// writes the landing page and a page for every project into the cache
function render_pages($project_list, $project_html, $bio_html, $config, $cache_dir) {
    $site = $config["site"];

    // the newest projects are shown first, projects without a date at the end
    usort($project_list, function($a, $b) {
        return project_timestamp($b) <=> project_timestamp($a);
    });

    // every page contains the landing page, this way the project page can be closed without loading anything
    $landing_html = render_template("landing.php", [
        "bio_html" => $bio_html,
        "project_list" => $project_list,
        "site" => $site,
        "core" => $config["core"],
        "has_legal" => in_array("legal", array_column($project_list, "id"), true)
    ]);

    write_page($cache_dir . "/" . PAGE_FOLDER . "/index.html", render_template("page.php", [
        "site" => $site,
        "page_id" => "",
        "title" => $site["title"],
        "description" => $site["description"],
        "url" => $site["url"] . "/",
        "image" => null,
        "landing_html" => $landing_html,
        "project" => null
    ]));

    echolog("rendered landing page", 2);

    foreach ($project_list as $project) {
        $preview = $project["preview"];

        write_page($cache_dir . "/" . PAGE_FOLDER . "/" . $project["id"] . "/index.html", render_template("page.php", [
            "site" => $site,
            "page_id" => $project["id"],
            "title" => $site["title"] . " // " . $project["name"],
            "description" => $project["description"],
            "url" => $site["url"] . "/" . $project["id"],
            "image" => $preview["is_placeholder"] ? null : $site["url"] . $preview["url"],
            "landing_html" => $landing_html,
            "project" => $project,
            "project_html" => $project_html[$project["id"]],
            "project_info" => project_info($project),
            "project_topics" => implode(", ", array_map("e", $project["topics"]))
        ]));

        echolog("rendered page of " . $project["id"], 2);
    }
}

function render_template($template, $variables) {
    extract($variables);

    ob_start();
    include(__DIR__ . "/../templates/" . $template);

    return ob_get_clean();
}

function write_page($file, $html) {
    if (!is_dir(dirname($file)) and !mkdir(dirname($file), 0777, true)) {
        throw new RuntimeException("failed to create folder " . dirname($file));
    }

    if (file_put_contents($file, $html) === FALSE) {
        throw new RuntimeException("failed to write " . $file);
    }
}

// the info line on top of a project page
function project_info($project) {
    $parts = [];

    $date = format_date($project["date"]);

    if ($date !== "") {
        array_push($parts, "Project started at <b>" . $date . "</b>");
    }

    $repo = $project["repo_based"] ? $project["owner"] . "/" . $project["id"] : $project["source"];

    if ($repo !== "") {
        array_push($parts, "<a href=\"//github.com/" . e($repo) . "\" target=\"_blank\">See on GitHub</a> (" . e($project["commit_count"]) . " commits)");
    }

    if (!empty($project["homepage"])) {
        array_push($parts, "<a href=\"" . e($project["homepage"]) . "\" target=\"_blank\">See homepage</a>");
    }

    return implode(" | ", $parts);
}

function project_timestamp($project) {
    $timestamp = empty($project["date"]) ? FALSE : strtotime($project["date"]);

    return $timestamp === FALSE ? 0 : $timestamp;
}

// same format as toLocaleDateString("de-DE"), e.g. 1.11.2023
function format_date($date) {
    $timestamp = empty($date) ? FALSE : strtotime($date);

    return $timestamp === FALSE ? "" : gmdate("j.n.Y", $timestamp);
}

// creates a color from a string, the result is identical to the former JavaScript implementation
// (based on http://jsfiddle.net/sUK45/), therefore the 32 bit integer overflow of JavaScript is emulated
function topic_color($topic) {
    $hash = 0;

    foreach (utf16_code_units($topic) as $code) {
        $hash = $code + (to_int32(to_int32($hash) << 5) - $hash);
    }

    $color = "#";

    for ($i = 0; $i < 3; $i++) {
        $color .= sprintf("%02x", (to_int32($hash) >> ($i * 8)) & 0xFF);
    }

    return $color;
}

function topic_text_color($background) {
    $sum = hexdec(substr($background, 1, 2)) + hexdec(substr($background, 3, 2)) + hexdec(substr($background, 5, 2));

    return $sum < 500 ? "#FFFFFF" : "#000000";
}

function to_int32($value) {
    $value = $value & 0xFFFFFFFF;

    return $value >= 0x80000000 ? $value - 0x100000000 : $value;
}

// JavaScript strings consist of UTF-16 code units, this is what charCodeAt() returns
function utf16_code_units($string) {
    $units = [];
    $chars = preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);

    // invalid UTF-8, fall back to the single bytes
    if ($chars === FALSE) {
        return array_values(unpack("C*", $string));
    }

    foreach ($chars as $char) {
        $bytes = array_values(unpack("C*", $char));

        switch (count($bytes)) {
            case 1: $code = $bytes[0]; break;
            case 2: $code = (($bytes[0] & 0x1F) << 6) | ($bytes[1] & 0x3F); break;
            case 3: $code = (($bytes[0] & 0x0F) << 12) | (($bytes[1] & 0x3F) << 6) | ($bytes[2] & 0x3F); break;
            default: $code = (($bytes[0] & 0x07) << 18) | (($bytes[1] & 0x3F) << 12) | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
        }

        // characters outside of the basic plane are stored as surrogate pairs
        if ($code > 0xFFFF) {
            $code -= 0x10000;

            array_push($units, 0xD800 + ($code >> 10), 0xDC00 + ($code & 0x3FF));
        } else {
            array_push($units, $code);
        }
    }

    return $units;
}

?>
