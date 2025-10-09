<?php

class Session {

    public function load_session_data() {
        $DB = new Database();

        $session_id = $_COOKIE['session'] ?? -1;

        if($session_id == -1) {
            define("SESS_USERID", -1);
            define("SESS_ID", null);
            return -1; // not logged in
        }

        $query = "SELECT * from sessions where sess_ident = :sess_ident";
        $binds = [
            "sess_ident" => $session_id
        ];

        $DB->query($query, $binds);

        define("SESS_USERID", $DB->RSArray[0]['userid']);
        define("SESS_IDENT", $_COOKIE['session']);
        define("SESS_ID", $DB->RSArray[0]['sess_id']);
        define("SESSIONDATA", $DB->RSArray[0]['sessiondata']);
    }

    public function create_session($userid) {
        $DB = new Database();

        $query = "BEGIN manage_session.create_Session(:userid); END;";
        $binds = [
            "userid" => $userid
        ];

        $DB->query($query, $binds);

        $query = "SELECT * from sessions where userid = :userid order by session_start desc fetch first 1 row only";
        $binds = [
            "userid" => $userid
        ];

        $DB->query($query, $binds);
        setcookie("session", $DB->RSArray[0]['sess_ident'], time() + $DB->RSArray[0]['expires'], "/");
    }
}