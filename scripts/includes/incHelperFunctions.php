<?php

function load_css($sheetname) {
    echo "<link rel='stylesheet' href='/styles/$sheetname.css'>";
}

function load_js($scripts = []) {
    for($i = 0; $i < count($scripts); $i++) {
        echo '<script src="/javascript/' . $scripts[$i] . '.js"></script>';
    }
}

function set_title($title) {
    echo '<title>' . $title . '</title>';
    echo '<script>$("#header").text("' . $title . '");</script>';
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

function load_essential_scripts() {
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>';
    echo '<script src="/javascript/main.js"></script>';
    echo "<link rel='stylesheet' href='/styles/main.css'>";
    echo '<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
          <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
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
