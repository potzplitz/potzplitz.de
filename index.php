<?php

require_once("scripts/includes/incCfg.php");

// === ROUTER === \\

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$static = new StaticFiles(__DIR__ . "/../static");
if ($static->serve($request)) {
    exit;
}

$DB = new Database();

$query = "SELECT * from routes where route = :route";
$binds = ["route" => $request];
$DB->query($query, $binds);

$route = null;
$selected_script = null;
$header = 0;
$params = [];
$found = false;

if ($DB->rows === 0) {
    $query = "SELECT * FROM routes";
    $DB->query($query, []);

    foreach ($DB->RSArray as $r) {
        $pattern = preg_replace('/\{[^\/]+\}/', '([^/]+)', $r['route']);
        $pattern = "@^" . $pattern . "$@";

        if (preg_match($pattern, $request, $matches)) {
            array_shift($matches);
            preg_match_all('/\{([^\/]+)\}/', $r['route'], $keys);

            $params = array_combine($keys[1], $matches);
            $params += json_decode($r["params"], true) ?? [];

            $selected_script = $r["script"];
            $header = (int)$r['header'];
            $route = $r;
            $found = true;
            break;
        }
    }
} else {
    $route = $DB->RSArray[0];
    $selected_script = $route["script"];
    $params = json_decode($route["params"], true) ?? [];
    $header = (int)$route["header"];
    $found = true;
}

if ($found && $selected_script) {
    define("PARAMS", $params);

    $query = "INSERT into log_loc (app, infokz, sess_id, datum) values (:route, 'route', :sess_id, sysdate)";
    $binds = [
        "route" => $request,
        "sess_id" => SESS_ID ?? -1
    ];

    $DB->query($query, $binds);

    if ($header === 1) {
        $Header = new Header();
        $Header->show_header();
        require_once("scripts/includes/incIcons.php");
    }

    if (str_starts_with($request, '/api/gd')) {
        require_once("scripts/api/gd_api/" . $selected_script . ".php");

    } else if (str_starts_with($request, '/api')) {
        require_once("scripts/api/" . $selected_script . ".php");

    } else {
        require_once("scripts/" . $selected_script . ".php");
    }

    $script = new $selected_script(PARAMS);
    $script->init();

} else {
    $query = "BEGIN manage_log_tables.ERROR(:app, :infokz, :error, :userid); END;";
    $binds = [
        "app" => "Router",
        "infokz" => $request,
        "error" => "Falsche Route! => IP: " . $_SERVER['REMOTE_ADDR'],
        "userid" => SESS_USERID ?? -1
    ];
    $DB->query($query, $binds);
}
