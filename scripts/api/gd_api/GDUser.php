<?php
class GDUser implements Routable {

    private static $keyMap = [

    ];

    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }

    public function init() {
        match($this->mode) {
            "request" => $this->request(array_merge(PARAMS, INS))
        };
    }

    private function request($inHash) {
        require_once("clsRobApi.php");

        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script("getGJUsers20.php");

        $response = $api->start_request([
            "str" => (string)$inHash['id'],
            "secret" => "Wmfd2893gb7"
        ]);

        echo $response;
    }
}