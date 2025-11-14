<?php

function load_css($sheetname) {
    global $returnVals;
    $returnVals .= "<link rel='stylesheet' href='/styles/$sheetname.css'>";
}

function load_js($scripts = []) {
    global $returnVals;
    for($i = 0; $i < count($scripts); $i++) {
        $returnVals .= '<script src="/javascript/' . $scripts[$i] . '.js"></script>';
    }
}

function set_title($title) {
    global $returnValsTitle;
    $returnValsTitle = $title;
}

function get_title() {
    global $returnValsTitle;

    return $returnValsTitle;
}

$returnMetaTags = '';

function set_meta_tags($tags, $type) {
    global $returnMetaTags;
    $returnMetaTags .= '<meta name="' . htmlspecialchars($type) . '" content="' . htmlspecialchars($tags) . '">' . PHP_EOL;
}

function get_meta_tags_custom() {
    global $returnMetaTags;
    return $returnMetaTags;
}


function dd($value) {
    var_dump($value);
    die;
}

function h($string) {
    return htmlspecialchars($string);
}

function debug_mail($value = "Debug Mail", $subject = "Nginx Debug") {
    $Mail = new Mail(DEBUG_MAIL);
    $Mail->setMessage($value);
    $Mail->setSubject($subject);
    $Mail->send();
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
}

function console_log($data) {
    echo "<script>console.log(" . json_encode($data) . ");</script>";
}

$__timers = [];

function timer_start($name) {
    global $__timers;
    $__timers[$name] = microtime(true);
}

function timer_end($name) {
    global $__timers;
    if (isset($__timers[$name])) {
        $elapsed = microtime(true) - $__timers[$name];
        $elapsed = number_format($elapsed, 2);
        echo "<!-- Timer [$name]: " . $elapsed . "s -->";
        return $elapsed;
    }
}

function display_info_message($message, $exit = false) {
    echo "<h2>" . $message . "</h2>";
    if($exit) {
        die;
    }
}

function echo_json($message) {
    if (!is_string($message)) {
        $message = json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    json_decode($message);
    if (json_last_error() === JSON_ERROR_NONE) {
        header("Content-Type: application/json; charset=utf-8");
        echo $message;
        exit;
    } else {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
}

function get_collected_assets() {
    global $returnVals;
    $assets = $returnVals;

    return $assets;
}