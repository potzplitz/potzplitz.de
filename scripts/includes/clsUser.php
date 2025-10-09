<?php

class User {
    public array $user = [];
    private $userid = -1;

    public function __construct($userid) {
        $this->userid = $userid;
    }
    public function load_user() {
        $DB = new Database();

        if($this->userid == -1) {
            return $user = ["userid" => -1];
        }
        
        $query = "SELECT * from t_users where userid = :userid";
        $binds = [
            "userid" => $this->userid
        ];
        $DB->query($query, $binds);

        $check_rsarray = $DB->RSArray[0]['userid'] ?? null;

        if($check_rsarray == null) {
            return $user = ["userid" => -1];
        }
        
        $user = [
            "userid" => $DB->RSArray[0]['userid'],
            "username" => $DB->RSArray[0]['username'],
            "email" => $DB->RSArray[0]['email']
        ];

        return $user;
    }
}