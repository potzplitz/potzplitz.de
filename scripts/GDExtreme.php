<?php
class GDExtreme implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match($this->mode) {
            "view_list" => $this->view_list(),
            "detail" => $this->show_detail(array_merge(INS, PARAMS)),
            "redirect" => $this->redirect_to_new_url(array_merge(INS, PARAMS)),
            default => null
        };
    }
    private function view_list() {
        $DB = new Database();
        $Template = new Template();
        $Template2 = new Template();

        set_title("Extreme Demon List");
        
        load_css("aredl");
        load_js(['aredl_check']);

        timer_start("AREDL_query");

        $query = "SELECT * FROM t_aredl a";
        $binds = [];

        $isChecked = filter_var(INS['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if($isChecked) {
            $query .= " join t_aredl_records r on a.id = r.level_id and r.user_id = :userid and r.progress > 99 and r.sart = 'AREDL' ";
            $binds['userid'] = SESS_USERID;
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

        $query = "SELECT * FROM t_aredl_records WHERE user_id = :userid AND progress > 99 AND sart = 'AREDL'";
        $DB->query($query, ['userid' => SESS_USERID]);
        $records = $DB->RSArray;

        $querytime = timer_end("AREDL_query");

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

            $level_url = $this->generate_level_url($level['raw_name'], $levelKey);

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
                "ATTEMPTS" => $attempts,
                "LEVEL_LINK" => $level_url
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
                $pageLinks .= '<a href="/geometrydash/extremedemons' . (!empty(INS['q']) ? '?q=' . urlencode(INS['q']) . '&' : '?') . 'page=' . $i . '" class="page-link">'.$i.'</a>';
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
            "LEVELCOUNT" => $ALLlevelcount,
            "QUERYTIME" => $querytime
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }

    private function show_detail($inHash) {
        $DB = new Database();
        $Template = new Template();

        $parts = explode('-', $inHash['id']);
        $levelid = end($parts);

        if(!(is_numeric($levelid))) {
            $this->level_not_found($levelid);
        }

        $Level = new Level($levelid, ListArt::AREDL);

        if(!$Level->exists()) {
            $this->level_not_found($levelid);
        }

        set_title("Extreme Demon List - " . $Level->name());
        load_css("aredl");
        
        // display_info_message("This page is still under construction!", false);

        $Template->load_template("/geometrydash/aredl_detail.php");
        $Template->load_hash([
            "IMAGE" => "https://levelthumbs.prevter.me/thumbnail/" . $Level->id() . "/small",
            "LEVELNAME" => $Level->name(),
            "CREATOR" => $Level->creator(),
            "PLACEMENT" => $Level->placement(),
            "VERIFIER" => $Level->verifier()
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function level_not_found($level = "") {
        set_title("Extreme Demon List");
        echo "<h2></h2>";
        display_info_message("Level <strong>" . $level . "</strong> was not found!", true);
        die;
    }

    private function redirect_to_new_url($params) {
        $DB = new Database();
        $DB->query("SELECT raw_name from t_aredl where id = :id", ['id' => $params['id']]);
        $new_url = $this->generate_level_url($DB->RSArray[0]['raw_name'], $params['id']);

        $new_url = rtrim(URL_HTBASE, "/") . $new_url;

        header("Location: " . $new_url, true, 301);
    }

    private function generate_level_url($levelname, $levelid) {
        $level_url = "/geometrydash/extremedemons/"; // maybe in component auslagern
        $level_url .= str_replace("_", "-", $levelname);
        $level_url .= "-" . $levelid;

        return $level_url;
    }
}