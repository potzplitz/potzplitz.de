<?php

class Startseite implements Routable {
    private $mode;
    public function __construct($mode) {
        $this->mode = $mode;
    }

    public function init() {
        set_title("Startseite");
        set_meta_tags("Homepage of potzplitz.de", "description");

        // CSS/JS nur hier definieren
        load_css("startseite");

        $Template = new Template();
        $Template->load_template("startseite/start.php");
        $Template->compile_template();
        $Template->show_template();
    }
}
