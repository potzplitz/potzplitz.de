<?php

class Startseite implements Routable {
    public function __construct($mode) {
        load_css("main");
        set_title("Startseite");
    }

    public function init() {
        echo urldecode(INS['message'] ?? '');
    }
}