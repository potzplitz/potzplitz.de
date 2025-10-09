<?php

class Startseite implements Routable {
    public function __construct($mode) {
        load_css("main");
        set_title("Startseite");
    }

    public function init() {
        echo urldecode(INS['message'] ?? '');
        load_css("startseite");

        $Template = new Template();
        $Template->load_template("startseite/start.php");
        $Template->compile_template();
        $Template->show_template();
    }
}