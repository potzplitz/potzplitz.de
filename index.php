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
$binds = [
    "route" => $request
];

$DB->query($query, $binds);

if ($DB->rows > 0) {
    $route = $DB->RSArray[0];

    $selected_script = $route["script"];
    define("PARAMS", json_decode($route["params"], true) ?? []);

    $query = "INSERT into log_loc (app, infokz, sess_id, datum) values (:route, 'route', :sess_id, sysdate)";
    $binds = [
        "route" => $request,
        "sess_id" => SESS_ID ?? -1
    ];

    if((int)$route['header'] == 1) {
        $Header = new Header();
        $Header->show_header();
    }

    $DB->query($query, $binds);

    if(substr($request, 1, 3) == 'api') {
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