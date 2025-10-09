<?php

interface Routable {
    public function __construct($mode);
    public function init();
}