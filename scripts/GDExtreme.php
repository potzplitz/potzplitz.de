<?php
class GDExtreme implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match($this->mode) {
            "view_list" => $this->view_list(),
            "detail" => $this->show_detail(INS),
            default => null
        };
    }
    private function view_list() {
        $DB = new Database();
        $Template = new Template();
        $Template2 = new Template();

        $query = "SELECT * FROM t_aredl a";
        $binds = [];

        $isChecked = filter_var(INS['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if($isChecked) {
            $query .= " join t_aredl_records r on a.id = r.level_id and r.user_id = :userid and r.progress > 99 and r.sart = 'AREDL' ";
            $binds['userid'] = SESS_USERID;
            $checked = false;
        }

        if(!empty(INS['q'])) {
            $q = urldecode(trim(strtolower(INS['q'])));

            $query .= " where (";

            if(is_numeric($q)) {
                $query .= "a.id = :query_id or ";
                $binds['query_id'] = $q;
            }
            
            $query .= " lower(a.name) like :query or lower(a.raw_name) like :query)";

            $binds['query'] = "%" . $q . "%";
        }

        $page = (INS['page'] ?? 1);
        $per_page = 100;
        $offset = ($page - 1) * $per_page;

        $DB->query($query, $binds);
        $ges_pages = ceil($DB->rows / $per_page);
        $ALLlevelcount = $DB->rows;

        $query .= " order by a.position asc offset :offset rows fetch first 100 rows only";
        $binds['offset'] = $offset;

        $DB->query($query, $binds);

        $page = (INS['page'] ?? 1);
        $page = max(1, min($page, $ges_pages ?: 1));

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
            $levelKey = $level['level_id'] ?? $level['id'];
            $found = in_array($levelKey, $completedIds);
            $attempts = $attemptsByLevel[$levelKey] ?? 0;

            $Template->load_hash([
                "LEVELNAME" => $level['name'],
                "LEVELNAME_RAW" => $level['raw_name'],
                "PLACEMENT" => $level['position'],
                "CREATOR" => $level['creator'],
                "ID" => $levelKey,
                "VERIFIER" => $level['verifier'],
                "THUMBNAIL" => "https://levelthumbs.prevter.me/thumbnail/" . $levelKey . "/small",
                "COUNTER" => $counter,
                "LEVELID" => $levelKey,
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
                $pageLinks .= '<a href="/gd/aredl' . (!empty(INS['q']) ? '?q=' . urlencode(INS['q']) . '&' : '?') . 'page=' . $i . '" class="page-link">'.$i.'</a>';
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
            "CHECKED" => ($isChecked ? "true" : "false"),
            "COMPLETED" => (($found ?? false) ? "completed" : ""),
            "LEVELCOUNT" => $ALLlevelcount
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }

    private function show_detail($inHash) {
        // TODO Router so umbauen dass er mit dynamischen /{id}/... routen umgehen kann

        //Da dann auch das große vorschaubild nehmen
    }
}