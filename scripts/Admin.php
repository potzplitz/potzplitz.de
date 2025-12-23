<?php

class Admin implements Routable {
    private $mode = "";

    private $ALLOWED_MODES = [];
    
    public function __construct($mode) {
        $this->load_admin_modes();
        $this->mode = $mode['mode'] ?? '';
    }
    
    public function init() {

        if(!$this->is_user_admin()) {
            // hier errorpage einbauen
            echo "<h1>You are not authorized to view this page!</h1>";
            http_response_code(401);
            return;
        }

        if (!in_array($this->mode, $this->ALLOWED_MODES, true)) {
            throw new InvalidArgumentException("Invalid admin mode");
        }
        
        $this->init_admin_function($this->mode);
    }

    private function load_admin_modes() {
        $DB = new Database();
        $DB->query("SELECT module from adminmodules", []);
        
        $this->ALLOWED_MODES = array_column($DB->RSArray, "module");
        $this->ALLOWED_MODES[] = "Index";
    }

    private function is_user_admin() {
        $User = new User(SESS_USERID);
        $User->load_user();

        return $User->isAdmin();
    }
    
    private function init_admin_function($function) {
        require_once("includes/objects/AdminModuleInterface.php");

        $filepath = __DIR__ . "/admin/" . basename($function) . ".php";

        if (!file_exists($filepath)) {
            throw new RuntimeException("Admin module not found");
        }
        
        require_once($filepath);

        if (!class_exists($function)) {
            throw new RuntimeException("Admin class not found");
        }
        
        $AdminFunction = new $function();

        if (!($AdminFunction instanceof AdminModuleInterface)) {
            throw new RuntimeException("Invalid admin module");
        }
        
        $AdminFunction->init();
    }
}