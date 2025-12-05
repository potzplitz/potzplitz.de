<?php
require_once("scripts/includes/incCfg.php");

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$DB = new Database();

// Route finden
$route = findRoute($DB, $request);

if (!$route) {
    // handle404($DB, $request);
    exit;
}

// logRoute($DB, $request);

// Script laden
$scriptPath = buildScriptPath($route['type'], $route['script']);
if (!file_exists($scriptPath)) {
    throw new RuntimeException("Script not found: {$scriptPath}");
}

require_once($scriptPath);

// Params definieren
define("PARAMS", $route['params']);

// Script instanziieren
$scriptClass = $route['script'];
$script = new $scriptClass(PARAMS);

// Route-Type basiertes Handling
switch ($route['type']) {
    case 'api':
    case 'api_gd':
        handleApiRequest($script);
        break;
        
    case 'page':
        handlePageRequest($script, $route);
        break;
        
    default:
        throw new RuntimeException("Unknown route type: {$route['type']}");
}

// === HELPER FUNCTIONS === //

function findRoute($DB, $request) {
    // Exakte Route suchen
    $query = "SELECT * FROM routes WHERE route = :route";
    $DB->query($query, ["route" => $request]);
    
    if ($DB->rows > 0) {
        $route = $DB->RSArray[0];
        $route['params'] = json_decode($route['params'], true) ?? [];
        return $route;
    }
    
    // Pattern-Matching
    $query = "SELECT * FROM routes";
    $DB->query($query, []);
    
    foreach ($DB->RSArray as $r) {
        $pattern = preg_replace('/\{[^\/]+\}/', '([^/]+)', $r['route']);
        $pattern = "@^" . rtrim($pattern, "/") . "/?$@";
        
        if (preg_match($pattern, $request, $matches)) {
            array_shift($matches);
            preg_match_all('/\{([^\/]+)\}/', $r['route'], $keys);
            
            $dbParams = json_decode($r["params"], true) ?? [];
            $urlParams = array_combine($keys[1], $matches);
            
            return [
                'script' => $r['script'],
                'type' => $r['type'],
                'header' => (int)$r['header'],
                'params' => array_merge($urlParams, $dbParams)
            ];
        }
    }
    
    return null;
}

function buildScriptPath($type, $script) {
    $basePaths = [
        'api' => "scripts/api/",
        'api_gd' => "scripts/api/gd_api/",
        'page' => "scripts/"
    ];
    
    $basePath = $basePaths[$type] ?? "scripts/";
    return $basePath . $script . ".php";
}

function handleApiRequest($script) {
    header('Content-Type: application/json');
    $script->init();
    exit;
}

function handlePageRequest($script, $route) {
    ob_start();
    $script->init();
    
    load_css("main");
    $pageContent = ob_get_clean();
    $title = get_title();
    
    $BODY_HEADER = "";
    if ((int)$route['header'] === 1) {
        require_once("scripts/Header.php");
        $Header = new Header();
        $BODY_HEADER = $Header->show_header($_SERVER['REQUEST_URI'], $title);
    }
    
    $Layout = new Template();
    $Layout->load_template("general/layout.php");
    $Layout->load_hash([
        "CONTENT" => $pageContent,
        "META_TAGS" => get_meta_tags_custom(),
        "ICONS" => file_get_contents("scripts/includes/incIcons.php"),
        "HEAD_HTML" => get_collected_assets(),
        "BODY_HEADER" => $BODY_HEADER,
        "TITLE" => $title
    ]);
    $Layout->compile_template();
    echo $Layout->get_output();
}

function logRoute($DB, $request) {
    $query = "INSERT INTO log_loc (app, infokz, sess_id, datum) 
              VALUES (:route, 'route', :sess_id, sysdate)";
    $binds = [
        "route" => $request,
        "sess_id" => SESS_ID ?? -1
    ];
    $DB->query($query, $binds);
}

function handle404($DB, $request) {
    $query = "BEGIN manage_log_tables.ERROR(:app, :infokz, :error, :userid); END;";
    $binds = [
        "app" => "Router",
        "infokz" => $request,
        "error" => "Falsche Route! => IP: " . $_SERVER['REMOTE_ADDR'],
        "userid" => SESS_USERID ?? -1
    ];
    $DB->query($query, $binds);
    
    http_response_code(404);
    echo "404 - Not Found";
}