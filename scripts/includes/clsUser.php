<?php

class User {
    public array $user = [];
    private $userid = -1;
    private $is_admin = false;
    private $is_logged_in = true;

    public function __construct($userid) {
        $this->userid = $userid;
    }
    public function load_user() {
        $DB = new Database();

        if($this->userid == -1) {
            $this->is_logged_in = false;
            return $user = ["userid" => -1];
        }
        
        $query = "SELECT * from t_users where userid = :userid";
        $binds = [
            "userid" => $this->userid
        ];
        $DB->query($query, $binds);

        $check_rsarray = $DB->RSArray[0]['userid'] ?? null;

        if($check_rsarray == null) {
            $this->is_logged_in = false;
            return $user = ["userid" => -1];
        }
        
        $user = [
            "userid" => $DB->RSArray[0]['userid'],
            "username" => $DB->RSArray[0]['username'],
            "email" => $DB->RSArray[0]['email'],
            "is_admin" => (int)(empty($DB->RSArray[0]['admin']) ? 0 : $DB->RSArray[0]['admin'])
        ];
        $this->user = $user;

        return $user;
    }

    public function isAdmin(): bool {
        return isset($this->user['is_admin']) && (int)$this->user['is_admin'] === 1;
    }

    public function isLoggedIn(): bool {
        return $this->is_logged_in;
    }
}