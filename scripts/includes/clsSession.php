<?php

class Session {

    public function load_session_data() {
        $DB = new Database();

        $session_id = $_COOKIE['session'] ?? -1;

        if($session_id == -1) { // not logged in
            define("SESS_USERID", -1);
            define("SESS_ID", null);
            return -1; 
        }

        $query = "SELECT * from sessions where sess_ident = :sess_ident";
        $binds = [
            "sess_ident" => $session_id
        ];

        $DB->query($query, $binds);

        if ($DB->rows < 1) { // session has expired
            define("SESS_USERID", -1);
            define("SESS_ID", null);
            define("SESS_IDENT", null);
            define("SESSIONDATA", null);
            return -1;
        }

        // session valid -> all ok

        define("SESS_USERID", $DB->RSArray[0]['userid']);
        define("SESS_IDENT", $_COOKIE['session']);
        define("SESS_ID", $DB->RSArray[0]['sess_id']);
        define("SESSIONDATA", $DB->RSArray[0]['sessiondata']);

        $query = "UPDATE t_users set lastlog = sysdate where userid = :userid";
        $binds = [
            "userid" => $DB->RSArray[0]['userid']
        ];

        $DB->query($query, $binds);
    }

    public function create_session($userid) {
        $DB = new Database();

        $query = "SELECT * from sessions where userid = :userid and expires > sysdate";
        $DB->query($query, ['userid' => $userid]);

        if($DB->rows > 0) {
            $sess_ident = $DB->RSArray[0]['sess_ident'];

        } else {
            $binds = [
                "userid" => $userid
            ];

            $query = "BEGIN manage_session.create_Session(:userid); END;";
            $DB->query($query, $binds);

            $query = "SELECT * from sessions where userid = :userid order by session_start desc fetch first 1 row only";
            $DB->query($query, $binds);

            $sess_ident = $DB->RSArray[0]['sess_ident'];
        }

        setcookie("session", $sess_ident, time() + 86400, "/");
    }
}