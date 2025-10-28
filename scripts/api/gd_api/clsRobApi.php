<?php 

enum Methods {
    case GET;
    case POST;
}
class RobApi {
    private Methods $method;
    private $script = "";
    private $keyMap = [];
    private $response = "";
    public function __construct() {
        require_once("scripts/includes/incGDKeys.php");
    }
    public function start_request(array $data) {

        $url = 'https://www.boomlings.com/database/' . $this->script;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method->name);

        if ($this->method === Methods::POST) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
        } elseif ($this->method === Methods::GET) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $response = 'cURL-Fehler: ' . curl_error($ch);
        }

        curl_close($ch);
        $this->response = $response;

        return $response;
    }

    public function set_rob_script($script) {
        $this->script = $script;
    }

    public function set_keymap(array $keyMap) {
        $this->keyMap = $keyMap;
    }

    public function set_request_method(Methods $method) {
        $this->method = $method;
    } 

    public function parse_api_response():string {
        $parts = explode('#', $this->response);
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
            $named[$this->keyMap[$k] ?? "unknown_$k"] = $v;
        }

        if (!empty($named['description_base64'])) {
            $named['description'] = base64_decode($named['description_base64']);
        }
        
        return json_encode($named, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}