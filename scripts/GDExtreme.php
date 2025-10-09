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

        $action = INS['action'] ?? 'search';
        
        $querycount = "SELECT COUNT(1) as cnt FROM t_aredl a";
        $query = "SELECT a.* FROM t_aredl a";
        $bindsCount = [];
        $binds = [];

        $template_action = "check";

        if($action == 'check') {
            $querycount .= " JOIN t_aredl_records r ON a.id = r.level_id 
                            AND r.user_id = :userid 
                            AND r.progress > 99 
                            AND r.sart = 'AREDL'";
            $query .= " JOIN t_aredl_records r ON a.id = r.level_id 
                        AND r.user_id = :userid 
                        AND r.progress > 99 
                        AND r.sart = 'AREDL'";

            $bindsCount['userid'] = SESS_USERID;
            $binds['userid'] = SESS_USERID;
            $template_action = "";
        }

        if (!empty(INS['q'])) {
            $search = trim(strtolower(INS['q']));
            
            $querycount .= " WHERE (LOWER(a.name) LIKE :levelname OR LOWER(a.raw_name) LIKE :levelname_raw";
            $query .= " WHERE (LOWER(a.name) LIKE :levelname OR LOWER(a.raw_name) LIKE :levelname_raw";

            $binds['levelname'] = "%{$search}%";
            $binds['levelname_raw'] = "%{$search}%";

            $bindsCount['levelname'] = "%{$search}%";
            $bindsCount['levelname_raw'] = "%{$search}%";

            if (ctype_digit($search)) {
                $querycount .= " OR a.id = :id)";
                $query .= " OR a.id = :id)";
                $binds['id'] = $search;
            } else {
                $querycount .= ")";
                $query .= ")";
            }
        }

        $DB2->query($querycount, $bindsCount);  
        $row = $DB2->RSArray[0];
        $ges_pages = ceil(($row['cnt'] ?? 0) / 100);

        $page = (INS['page'] ?? 1);
        $page = max(1, min($page, $ges_pages ?: 1));

        $per_page = 100;
        $offset = ($page - 1) * $per_page;

        $query .= " ORDER BY a.position ASC OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        $binds['offset'] = $offset;
        $binds['limit'] = $per_page;

        $DB->query($query, $binds);

        $levels = $DB->RSArray;
        $levelcount = $DB->rows;

        $Template->load_template("geometrydash/aredl_row.php");
        $Template2->load_template("geometrydash/aredl.php");
        set_title("Extreme Demon List");

        load_css("aredl");
        load_js(['aredl_check']);

        $query = "SELECT * FROM t_aredl_records WHERE user_id = :userid AND progress > 99 AND sart = 'AREDL'";
        $DB->query($query, ['userid' => SESS_USERID]);
        $records = $DB->RSArray;

        $attemptsByLevel = [];
        foreach ($records as $rec) {
            $attemptsByLevel[$rec['level_id']] = $rec['attempts'];
        }

        $completedIds = array_keys($attemptsByLevel);

        $list = "";
        $counter = 0;

        foreach ($levels as $level) {
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
        $range = ($ges_pages < 3 ? $ges_pages : 3);

        for ($i = max(1, $page - $range); $i <= min($ges_pages, $page + $range); $i++) {
            if ($i == $page) {
                $pageLinks .= '<span class="page-link active">' . $i . '</span>';
            } else {
                $pageLinks .= '<a href="/gd/aredl' . (!empty(INS['q']) ? '?q=' . INS['q'] . '&' : '?') . 'page=' . $i . '" class="page-link">'.$i.'</a>';
            }
        }

        $Hash['DISP_PAGES'] = "";
        if ($levelcount < 1) {
            $list = "<h2 style='width: 100%; text-align: center;'>No Levels found!</h2>";
            $pageLinks = "";
            $Hash['DISP_PAGES'] = "hidden";
        } elseif ($ges_pages < 2) {
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
            "QUERY" => INS['q'] ?? '',
            "TEMP_ACTION" => $template_action
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }

    private function show_detail($levelid) {
        // TODO Router so umbauen dass er mit dynamischen /{id}/... routen umgehen kann
    }
}