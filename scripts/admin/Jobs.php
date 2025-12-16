<?php

class Jobs implements AdminModuleInterface {

    private const DB_JOBS = [
        "AREDL" => [
            "BUTTONTEXT" => "Start",
            "NAME" => "All Rated Extreme Demon List",
            "JOBID" => "AREDL"
        ],
        "NCDL" => [
            "BUTTONTEXT" => "Start",
            "NAME" => "Nine Circles Demon List",
            "JOBID" => "NCDL"
        ]
    ];

    public function init() {
        set_title("DB Jobs");
        load_js(["admin_jobs"]);
        $this->show_page();

    } 

    private function show_page() {
        $Template = new Template();
        $Template2 = new Template();

        $buttonhtml = "";

        foreach (self::DB_JOBS as $jobData) {
            $Template->load_template("admin/jobs_row.php");
            $Template->load_hash([
                "JOBNAME"    => $jobData['NAME'],
                "BUTTONTEXT" => $jobData["BUTTONTEXT"],
                "JOBID" => $jobData['JOBID'],
            ]);
            $Template->compile_template();
            $buttonhtml .= $Template->get_output();
        }


        $Template2->load_template("admin/jobs.php");
        $Template2->load_hash([
            "TABLE" => $buttonhtml
        ]);
        $Template2->compile_template();
        $Template2->show_template();
    }

}