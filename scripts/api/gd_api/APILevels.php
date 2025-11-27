<?php
class APILevels implements Routable {
    private $keyMap = [
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
            "request" => $this->request(array_merge(PARAMS, INS))
        };
    }

    public function request($inHash) {
        require_once("clsRobApi.php");
        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script("getGJLevels21.php");
        $api->set_keymap($this->keyMap);
        
        $api->start_request([
            "str" => (string)$inHash['id'],
            "star" => 1,
            "type" => 0,
            "secret" => "Wmfd2893gb7"
        ]);
        
        echo_json($api->parse_api_response());

        // $response = $api->parse_api_response();
        // $response = json_decode($response, true);

        // $url = 'https://history.geometrydash.eu/api/v1/date/level/' . $inHash['id'];
        // $history_response = file_get_contents($url);

        // if($history_response === false) {
        //     throw new Exception("Error while fetching level History Data (upload time)");
        // }

        // $data = json_decode($history_response, true);
        // $data = $data['low']['estimation'];

        // $response = array_merge($response, ["upload_date" => $data]);

        // header("Content-Type: application/json");
        // echo json_encode($response, JSON_PRETTY_PRINT);
    }
}