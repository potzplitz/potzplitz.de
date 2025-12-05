<?php

class Index implements AdminModuleInterface {
    private $moduledata = [];
    public function init() {
        set_title("Admin Tools");
        $this->showModules();
    } 

    public function showModules() {
        $Template = new Template();
        $Template2 = new Template();

        $this->loadModuleData();

        $rows = "";
        foreach($this->moduledata as $module) {
            $Template->load_template("admin/index_row.php");
            $Template->load_hash([
                "PANELROUTE" => $module['route'],
                "PANELNAME" => $module['module'],
                "PANELDESCRIPTION" => $module['description']
            ]);
            
            $Template->compile_template();
            $rows .= $Template->get_output();
        }

        $Template2->load_template("admin/index.php");
        $Template2->load_hash([
            "PANELROWS" => $rows
        ]);
        $Template2->compile_template();
        $Template2->show_template();
    }

    private function loadModuleData() {
        $DB = new Database();

        $query = "SELECT * from adminmodules";
        $DB->query($query, []);

        $this->moduledata = $DB->RSArray;
    }
}