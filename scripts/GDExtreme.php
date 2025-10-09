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

        $querycount = "SELECT * from t_aredl";
        $DB2->query($querycount, []);
        $ges_pages = ceil($DB2->rows / 100);

        $page = (INS['page'] ?? 1);

        if($page > $ges_pages) {
            $page = $ges_pages;
        } else if($page < 1) {
            $page = 1;
        }

        $per_page = 100;
        $offset = (max($page, 1) - 1) * $per_page;

        $query = "SELECT * from t_aredl";

        $binds = [
            "offset" => $offset
        ];
        
        if(!empty(INS['q'])) {
            $query .= " where lower(name) like :levelname";
            $binds['levelname'] = '%' . strtolower(INS['q']) . '%';
        }
        
        $query .= " order by position asc offset :offset rows fetch first 100 rows only";

        $DB->query($query, $binds);

        $levels = $DB->RSArray;
        $levelcount = $DB->rows;

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

        $pageLinks = '';
        $range = 3;

        for ($i = max(1, $page - $range); $i <= min($ges_pages, $page + $range); $i++) {
            if ($i == $page) {
                $pageLinks .= '<span class="page-link active">' . $i . '</span>';
            } else {
                $pageLinks .= '<a href="/gd/aredl' . (!empty(INS['q']) ? '?q=' . INS['q'] . '&' : '?') . 'page=' . $i . '" class="page-link">'.$i.'</a>';
            }
        }

        $Hash['DISP_PAGES'] = "";
        if($levelcount < 1) {
            $list = "<h2 style='width: 100%; text-align: center;'>No Levels found!</h2>";
            $pageLinks = "";
            $Hash['DISP_PAGES'] = "hidden";

        } else if($ges_pages < 2) {
            $pageLinks = "";
            $Hash['DISP_PAGES'] = "hidden";
        }

        $Template2->load_hash([
            "LIST" => $list,
            "NEXT_PAGE" => $page + 1,
            "PREV_PAGE" => $page - 1,
            "CURRENT_PAGE" => $page,
            "DISP_NEXT_PAGE" => (($ges_pages <= $page) ? "hidden" : ""),
            "DISP_PREV_PAGE" => (($page <= 1) ? "hidden" : ""),
            "MAX_PAGES" => $ges_pages,
            "PAGE_LINKS" => $pageLinks,
            "DISP_PAGES" => $Hash['DISP_PAGES'],
            "QUERY" => INS['q'] ?? ''
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }
    private function show_detail($levelid) {
        // TODO Router so umbauen dass er mit dynamischen /{id}/... routen umgehen kann
    }
}