<?php
class GDExtreme implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match($this->mode) {
            "view_list" => $this->view_list()
        };
    }
    private function view_list() {
        $DB = new Database();
        $DB2 = new Database();
        $Template = new Template();
        $Template2 = new Template();

        $page = (INS['page'] ?? 1);
        $per_page = 100;
        $offset = (max($page, 1) - 1) * $per_page;


        $querycount = "SELECT * from t_aredl";
        $DB2->query($querycount, []);
        $ges_pages = ceil($DB2->rows / 100);

        $query = "SELECT * from t_aredl order by position asc offset :offset rows fetch first 100 rows only";
        $binds = [
            "offset" => $offset
        ];

        $DB->query($query, $binds);

        $levels = $DB->RSArray;

        $Template->load_template("geometrydash/aredl_row.php");
        $Template2->load_template("geometrydash/aredl.php");
        set_title("Extreme Demon List");

        load_css("aredl");
        load_js(['aredl_check']);

        $list = "";
        $counter = 0;

        $query = "SELECT * from t_aredl_records where user_id = :userid and progress > 99 and sart = 'AREDL'";
        $binds = [
            "userid" => SESS_USERID
        ];
        $DB->query($query, $binds);
        $records = $DB->RSArray;

        $attemptsByLevel = [];
        foreach ($records as $rec) {
            $attemptsByLevel[$rec['level_id']] = $rec['attempts'];
        }

         $completedIds = array_keys($attemptsByLevel);
        
        foreach($levels as $level) {
            $counter++;

            $found = in_array($level['id'], $completedIds);
            $attempts = $attemptsByLevel[$level['id']] ?? 0;

            $Template->load_hash([
                "LEVELNAME" => $level['name'],
                "LEVELNAME_RAW" => $level['raw_name'],
                "PLACEMENT" => $level['position'],
                "CREATOR" => $level['creator'],
                "ID" => $level['id'],
                "VERIFIER" => $level['verifier'],
                "THUMBNAIL" => "https://levelthumbs.prevter.me/thumbnail/" . $level['id'] . "/small",
                "COUNTER" => $counter,
                "LEVELID" => $level['id'],
                "COMPLETED" => ($found ? "completed" : ""),
                "BUTTONTEXT" => ($found ? "uncheck" : "check"),
                "ATTEMPTS" => $attempts
            ]);

            $Template->compile_template();
            $list .= $Template->get_output();
        }

        $Template2->load_hash([
            "LIST" => $list,
            "NEXT_PAGE" => $page + 1,
            "PREV_PAGE" => $page - 1,
            "CURRENT_PAGE" => $page,
            "DISP_NEXT_PAGE" => (($ges_pages <= $page) ? "hidden" : ""),
            "DISP_PREV_PAGE" => (($page <= 1) ? "hidden" : "")
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }
    private function show_detail($levelid) {
        // TODO Router so umbauen dass er mit dynamischen /{id}/... routen umgehen kann
    }
}