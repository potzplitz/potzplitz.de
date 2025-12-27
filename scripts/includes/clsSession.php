<?php

class Session {
private function getSessionCookie(): ?string {
    return $_COOKIE['session'] ?? null;
}

private function getPersistentCookie(): ?string {
    return $_COOKIE['persistent'] ?? null;
}

public function load_session_data() {
    $DB = new Database();

    $session_id = $this->getSessionCookie() ?? -1;

    // check if a session cookie exists
    if($session_id == -1) { 
        // no session cookie found

        // check if user has a valid persistent session in cookie
        $query = "SELECT userid from sessions_persistent where lower(token_hash) = :hash";
        $binds = [
            "hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", "", false)
        ];

        $DB->query($query, $binds);

        // check if user has a valid persistent session
        if(!$this->validate_persistent_session($DB->RSArray[0]['userid'] ?? -1) && $DB->rows < 1) { // invalid persistent session => finally log out user
            define("SESS_USERID", -1);
            define("SESS_ID", null);
            return -1; 

        } else { // valid persistent session => renew/restore session and regenerate persistent session
            $this->regenerate_persistent_session($DB->RSArray[0]['userid']);
            $this->create_session($DB->RSArray[0]['userid']);

            // fetch session data from current new session
            $query = "SELECT * from sessions where userid = :userid 
                        order by session_start desc fetch first 1 row only";

            $binds = [
                "userid" => $DB->RSArray[0]['userid']
            ];

            $DB->query($query, $binds);

            // load session data and set constants
            define("SESS_USERID", $DB->RSArray[0]['userid']);
            define("SESS_IDENT", $DB->RSArray[0]['sess_ident']);
            define("SESS_ID", $DB->RSArray[0]['sess_id']);

            // update user last login
            $query = "UPDATE t_users set lastlog = sysdate where userid = :userid";
            $binds = [
                "userid" => $DB->RSArray[0]['userid']
            ];

            $DB->query($query, $binds);
            
            // user is logged in => return
            return;
        }
    }

    // user has an existing session cookie => probably not expired or hijack attempt

    // check if session is valid => expired yes no
    $query = "SELECT * from sessions where sess_ident = :sess_ident";
    $binds = [
        "sess_ident" => $session_id
    ];

    $DB->query($query, $binds);

    // session expired check 
    if(strtotime($DB->RSArray[0]['expires'] ?? 0) < time()) {
        $this->invalidate_session($session_id); // make current session from cookie invalid
    }

    // execute statement again because session got altered (idk if this is necessary but it works so im keeping it ;_;)
    $DB->query($query, $binds);

    // check if session in cookie is expired
    if((int)(empty($DB->RSArray[0]['valid']) ? 0 : $DB->RSArray[0]['valid']) == 0) { // session has expired

        // check if user has a valid persistent session in cookie
        $query = "SELECT userid from sessions_persistent where lower(token_hash) = :hash";
        $binds = [
            "hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", "", false)
        ];

        $DB->query($query, $binds);

        // validate persistent session cookie
        if(!$this->validate_persistent_session($DB->RSArray[0]['userid'] ?? -1)) { // invalid persistent session => finally log out user
            define("SESS_USERID", -1);
            define("SESS_ID", null);
            define("SESS_IDENT", null);

            return -1;
            
        } else { // valid persistent session => renew/restore session and regenerate persistent session
            $this->regenerate_persistent_session($DB->RSArray[0]['userid']);
            $this->create_session($DB->RSArray[0]['userid']);

            // fetch session data from current new session
            $query = "SELECT * from sessions where userid = :userid 
                        order by session_start desc fetch first 1 row only";

            $binds = [
                "userid" => $DB->RSArray[0]['userid']
            ];
            
            $DB->query($query, $binds);

            // load session data and set constants
            define("SESS_USERID", $DB->RSArray[0]['userid']);
            define("SESS_IDENT", $DB->RSArray[0]['sess_ident']);
            define("SESS_ID", $DB->RSArray[0]['sess_id']);

            // update user last login
            $query = "UPDATE t_users set lastlog = sysdate where userid = :userid";
            $binds = [
                "userid" => $DB->RSArray[0]['userid']
            ];

            $DB->query($query, $binds);
            
            // user is logged in => return
            return;
        }
    }

    // session in cookie is valid => no renewal or invalidating => all ok so login directly
    define("SESS_USERID", $DB->RSArray[0]['userid']);
    define("SESS_IDENT", $this->getSessionCookie());
    define("SESS_ID", $DB->RSArray[0]['sess_id']);

    // update user last login
    $query = "UPDATE t_users set lastlog = sysdate where userid = :userid";
    $binds = [
        "userid" => $DB->RSArray[0]['userid']
    ];

    $DB->query($query, $binds);
}

    public function validate_persistent_session($userid) {
        $DB = new Database();

        $persistent = $this->getPersistentCookie() ?? null;
        
        if($persistent === null) {// dd("test1");
            return false;
        }

        $query = "SELECT * from sessions_persistent where userid = :userid and valid = 1 
                    and expires_at > sysdate order by created_at desc fetch first 10 rows only";

        $binds = [
            "userid" => $userid
        ];

        $DB->query($query, $binds);

        if($DB->rows < 1) {// dd("test2");
            return false;
        }

        // iterate through result set of 10 and check if persistent session is ok
        $expected = hash_hmac('sha256', $persistent, "", false);// dd($persistent);
        foreach($DB->RSArray as $row) {
            if(hash_equals($expected, strtolower($row['token_hash']))) {// dd("test3");
                return true;
            }
        }
        // dd("test4");
        return false;
    }

    public function create_persistent_session($userid) { // gets called from outside this class (in login function in Account management class)
        $DB = new Database();

        // generate persistent session identity hashed to raw hex values
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash_hmac('sha256', $token, "", false);

        // insert persistent session into DB
        $query = "INSERT into sessions_persistent (userid, expires_at, token_hash, created_at, valid, agent) values 
                    (:userid, sysdate + interval '1' year, :token_hash, sysdate, 1, :useragent)";
        $binds = [
            "userid" => $userid,
            "token_hash" => $tokenHash,
            "useragent" => $this->getDeviceType()
        ];

        $DB->query($query, $binds);

        // cookie valid for 1 year
        $time = new DateTime();
        $time->modify("+1 year");

        $timestamp = $time->getTimestamp();

        setcookie("persistent", $token, $timestamp, "/");
    }

    private function regenerate_persistent_session($userid) {
        $DB = new Database();

        if(empty($this->getPersistentCookie())) {
            return;
        }

        // invalidate old persistent session
        $query = "UPDATE sessions_persistent set valid = 0 where token_hash = :token_hash and userid = :userid";
        $binds = [
            "token_hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", "", false),
            "userid" => $userid
        ];

        $DB->query($query, $binds);

        // unset old persistent cookie
        setcookie("persistent", "unset", time() - 86400, "/");

        // generate and set new persistent cookie => write in cookie and DB
        $this->create_persistent_session($userid);
    }

    public function create_session($userid) {
        $DB = new Database();

        $binds = [
            "userid" => $userid,
            "referrer" => $this->getReferrer(),
            "agent" => $this->getDeviceType()
        ];

        $query = "BEGIN manage_session.create_Session(:userid, :referrer, :agent); END;";
        $DB->query($query, $binds);

        $query = "SELECT * from sessions where userid = :userid 
                    order by session_start desc fetch first 1 row only";

        $DB->query($query, $binds);

        $sess_ident = $DB->RSArray[0]['sess_ident'];

        setcookie("session", $sess_ident, time() + 86400, "/");
    }

    private function invalidate_session($session) {
        $DB = new Database();

        $query = "UPDATE sessions set valid = 0 where sess_ident = :sess_ident";
        $binds = [
            "sess_ident" => $session
        ];

        $DB->query($query, $binds);
    }

    public function getDeviceType() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
    }

    public function getReferrer() {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $parts = parse_url($_SERVER['HTTP_REFERER']);

            return $parts['host'] ?? null;

        } else {
            return "none";
        }
    }
}