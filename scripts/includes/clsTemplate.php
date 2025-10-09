<?php

class Template {

    private $hash = array();
    private $template = '';
    private $finished_template;
    private $title = "";

    public function load_hash($placeholder = []) {
        $this->hash = $placeholder;
    }

    public function load_template($template) {
        $this->template = $this->title;
        $this->template .= file_get_contents(DIR_ROOT . "/templates/" . $template);
    }

    public function compile_template() {
        $this->finished_template = preg_replace_callback('/<\-(.*?)\->/', function($matches) {
            $key = trim($matches[1]);
            if (isset($this->hash[$key])) {
                return $this->hash[$key];
            }
            return $matches[0];
        }, $this->template);
    }

    public function get_output() {
        return $this->finished_template;
    }

    public function set_title($title) {
        $this->title = "<title>$title</title>";
    }

    public function show_template() {
        echo $this->finished_template;
    }
}
