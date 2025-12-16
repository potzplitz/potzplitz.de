<?php

class ManageJob implements Routable {
    private $mode = "";
    private const JOBS = [
        "AREDL" => "manage_api_data.get_AREDL",
        "NCDL" => "manage_api_data.get_NCL"
    ];

    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {

        $this->isRequesterAllowed();
        $this->validateJob(INS);

        match($this->mode) {
            "start_job" => $this->startJob(INS),
            "get_job_status" => $this->getJobStatus(INS)
        };
    }

    private function validateJob($inHash) {
        if(!array_key_exists($inHash['jobname'], self::JOBS)) {
            echo_json(["error" => "Unknown Job!"]);
            exit();
        }
    }

    private function isRequesterAllowed() {
        $User = new User(SESS_USERID);
        $User->load_user();
        if(!$User->isAdmin()) {
            echo_json(["error" => "You are not allowed to use this endpoint!"]);
            die;
        }
    }

    private function startJob($inHash) {
        $DB = new Database();
        $query = "BEGIN " . self::JOBS[$inHash['jobname']] . "(); END;";

        if($DB->query($query, [])) {
            echo_json(["statuscode" => "200"]);
            exit();

        } else {
            echo_json(["statuscode" => "500"]);
            debug_mail("Job Starten failed! | Job Name: " . $inHash['jobname']);
            exit();
        }
    }

    private function getJobStatus($inHash) {
        $DB = new Database();

        $query = "SELECT status, last_start, last_finish, (last_finish - last_start) * 86400 AS duration from t_status where process_name = :name";
        $binds = [
            "name" => $inHash['jobname']
        ];

        $DB->query($query, $binds);
        echo_json([
            'status' => $DB->RSArray[0]['status'], 
            'last_start' => $DB->RSArray[0]['last_start'], 
            'last_finished' => $DB->RSArray[0]['last_finish'],
            "duration" => $DB->RSArray[0]['duration']
        ]);
    }
}