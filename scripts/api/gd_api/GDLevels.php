<?php
class GDLevels implements Routable {
    private static $keyMap = [
        1  => 'level_id',
        2  => 'level_name',
        5  => 'level_version',
        6  => 'creator_id',
        8  => 'difficultyDenominator',
        9  => 'level_difficulty',
        10 => 'downloads',
        12 => 'audio_track',
        13 => 'game_version',
        14 => 'likes',
        17 => 'is_demon',
        43 => 'demon_difficulty',
        25 => 'is_auto',
        18 => 'stars',
        19 => 'featured_score',
        42 => 'is_epic',
        45 => 'object_count',
        3  => 'description_base64',
        15 => 'two_player_mode',
        30 => 'song_id',
        31 => 'is_ldm',
        37 => 'coins_verified',
        38 => 'password',
        39 => 'level_length',
        46 => 'is_cp_verified',
        47 => 'is_copied',
        35 => 'record_id'
    ];
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }

    public function init() {
        match($this->mode) {
            "request" => $this->request(INS)
        };
    }

    public function request($inHash) {
        require_once("clsRobApi.php");
        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script("getGJLevels21.php");
        
        $response = $api->start_request([
            "str" => (string)$inHash['levelid'],
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