<?php
class GDLevels implements Routable {

    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }

    public function init() {
        match($this->mode) {
            "request" => $this->request(array_merge(PARAMS, INS))
        };
    }

    public function request($inHash) {
        require_once("clsRobApi.php");
        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script("getGJLevels21.php");
        
        $response = $api->start_request([
            "str" => (string)$inHash['id'],
            "star" => 1,
            "type" => 0,
            "secret" => "Wmfd2893gb7"
        ]);

        $parts = explode('#', $response);
        $main = $parts[0];
        $segments = explode(':', $main);

        $data = [];
        for ($i = 0; $i < count($segments) - 1; $i += 2) {
            $key = trim($segments[$i]);
            $value = isset($segments[$i + 1]) ? trim($segments[$i + 1]) : '';
            if ($key !== '') {
                $data[$key] = $value;
            }
        }

        $named = [];
        foreach ($data as $k => $v) {
            $named[self::$keyMap[$k] ?? "unknown_$k"] = $v;
        }

        if (!empty($named['description_base64'])) {
            $named['description'] = base64_decode($named['description_base64']);
        }

        header("Content-Type: application/json");
        echo json_encode($named, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}