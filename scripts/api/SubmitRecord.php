<?php

class SubmitRecord implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match($this->mode) {
            "submit" => $this->submit(INS)
        };
    }
    private function submit($inHash = []) { // gets called via site
        $DB = new Database();

        if(ARR_USERINFO['userid'] == -1) {
            echo json_encode(["message" => "you need to be logged in to do that!"]);
            return;
        }

        $query = "SELECT * from t_levelrecords where user_id = :userid and level_id = :level_id and sart = :sart";
        $binds = [
            "userid" => ARR_USERINFO['userid'],
            "level_id" => $inHash['levelid'],
            "sart" => $inHash['sart']
        ];

        $DB->query($query, $binds);

        if($DB->rows > 0) {
            $query = "DELETE from t_levelrecords where user_id = :userid and level_id = :level_id and sart = :sart";
            $binds = [
                "userid" => ARR_USERINFO['userid'],
                "level_id" => $inHash['levelid'],
                "sart" => $inHash['sart']
            ];

            $DB->query($query, $binds);

            $checked = 0;
        } else {
            $query = "INSERT into t_levelrecords (user_id, level_id, progress, attempts, submit_date, verified, sart) values 
                    (:userid, :level_id, :progress, :attempts, sysdate, :verified, :sart)";
            
            $binds = [
                "userid" => ARR_USERINFO['userid'],
                "level_id" => $inHash['levelid'],
                "progress" => $inHash['progress'],
                "attempts" => $inHash['attempts'],
                "verified" => 0,
                "sart" => $inHash['sart']
            ];

            $DB->query($query, $binds);

            $checked = 1;
        }
        echo json_encode(["checked" => $checked]);
    }
}