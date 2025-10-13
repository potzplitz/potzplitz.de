<?php 

enum Methods {
    case GET;
    case POST;
}
class RobApi {
    private Methods $method;
    private $script = "";
    public function __construct() {
        require_once("/scripts/includes/incGDKeys.php");
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

        return $response;
    }

    public function set_rob_script($script) {
        $this->script = $script;
    }

    public function set_request_method(Methods $method) {
        $this->method = $method;
    } 
}