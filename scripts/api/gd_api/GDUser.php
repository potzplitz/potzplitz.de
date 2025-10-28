<?php
class GDUser implements Routable {
    private $keyMap = [ // GetGJUsers20.php
        1  => 'userName',
        2  => 'userID',
        3  => 'stars',
        4  => 'demons',
        6  => 'ranking',
        7  => 'accountHighlight',
        8  => 'creatorpoints',
        9  => 'iconID',
        10 => 'color',
        11 => 'color2',
        13 => 'secretCoins',
        14 => 'iconType',
        15 => 'special',
        16 => 'accountID',
        17 => 'usercoins',
        18 => 'messageState',
        19 => 'friendsState',
        20 => 'youTube',
        21 => 'accIcon',
        22 => 'accShip',
        23 => 'accBall',
        24 => 'accBird',
        25 => 'accDart',
        26 => 'accRobot',
        27 => 'accStreak',
        28 => 'accGlow',
        29 => 'isRegistered',
        30 => 'globalRank',
        31 => 'friendstate',
        38 => 'messages',
        39 => 'friendRequests',
        40 => 'newFriends',
        41 => 'NewFriendRequest',
        42 => 'age',
        43 => 'accSpider',
        44 => 'twitter',
        45 => 'twitch',
        46 => 'diamonds',
        48 => 'accExplosion',
        49 => 'modlevel',
        50 => 'commentHistoryState',
        51 => 'color3',
        52 => 'moons',
        53 => 'accSwing',
        54 => 'accJetpack',
        55 => 'demons_breakdown',
        56 => 'classicLevels',
        57 => 'platformerLevels'
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
        $api->set_keymap($this->keyMap);

        $api->start_request([
            "str" => (string)$inHash['id'],
            "secret" => "Wmfd2893gb7"
        ]);

        echo_json($api->parse_api_response());
    }
}