<?php
class GDLevels implements Routable {
    private $mode = "";
    private $viewMode = "aredl";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match($this->mode) {
            "view_list" => $this->view_list(),
            "detail" => $this->show_detail(array_merge(INS, PARAMS)),
            "redirect" => $this->redirect_to_new_url(array_merge(INS, PARAMS)),
            "ncl" => $this->setNCLMode($this->mode),
            default => null
        };
    }

    private function setNCLMode($mode) {
        $this->viewMode = "ncl";
        if((PARAMS['mode2'] ?? "") == "detail") {
            $this->show_detail(array_merge(INS, PARAMS));
        } else {
            $this->view_list();
        }
    }

    private function view_list() {
        $DB = new Database();
        $Template = new Template();
        $Template2 = new Template();

        if($this->viewMode == "aredl") {
            set_title("Extreme Demon List");
        } else {
            set_title("Nine Circles Demon List");
        }

        load_css("aredl");
        load_js(['levellist_check']);

        timer_start("LEVEL_query");

        $query = "SELECT * FROM t_" . $this->viewMode . " a";
        $binds = [];

        $isChecked = filter_var(INS['checked'] ?? false, FILTER_VALIDATE_BOOLEAN); // filter für schon geschafft gechecked
        $checkedUncompleted = filter_var(INS['uncompleted'] ?? false, FILTER_VALIDATE_BOOLEAN); // filter für den nicht geschafft checked

        if($isChecked && SESS_USERID != -1) {
            $checkedUncompleted = false;
            $query .= " join t_levelrecords r on a.id = r.level_id and r.user_id = :userid and r.progress > 99 and r.sart = '" . strtoupper($this->viewMode) . "' ";
            $binds['userid'] = SESS_USERID;

        } else if($checkedUncompleted && SESS_USERID != -1) {
            $isChecked = false;
            $query .= " left join t_levelrecords r on a.id = r.level_id and r.user_id = :userid and r.sart = '" . strtoupper($this->viewMode) . "' ";
            $binds['userid'] = SESS_USERID;
        
        } else {
            $isChecked = false;
            $checkedUncompleted = false;
        }

        if(!empty(INS['q'])) {
            $q = urldecode(trim(strtolower(INS['q'])));

            $query .= " where (";

            if(is_numeric($q)) {
                $query .= "a.id = :query_id or ";
                $binds['query_id'] = $q;
            }

            $query .= " lower(a.name) like :query or lower(a.raw_name) like :query)";

            if($checkedUncompleted) {
                $query .= " and (r.progress <= 99 or r.progress is null)";
            }

            $binds['query'] = "%" . $q . "%";
        } else {
            if($checkedUncompleted) {
                $query .= " where (r.progress <= 99 or r.progress is null)";
            }
        }

        $page = (INS['page'] ?? 1);
        $per_page = 100;
        $offset = ($page - 1) * $per_page;

        $DB->query($query, $binds);
        $ges_pages = ceil($DB->rows / $per_page);
        $ALLlevelcount = $DB->rows;

        if($this->viewMode == "aredl") {
            $query .= " order by a.position asc";
        } else {
            $query .= " order by (select case when a.position = 'TBA' then 999999 else to_number(a.position) end)";
        }

        $query .= " offset :offset rows fetch first 100 rows only";

        $binds['offset'] = $offset;

        $DB->query($query, $binds); // execute built sql query

        $page = (INS['page'] ?? 1);
        $page = max(1, min($page, $ges_pages ?: 1));

        $levels = $DB->RSArray;
        $levelcount = $DB->rows;

        $Template->load_template("geometrydash/levellist_row.php");
        $Template2->load_template("geometrydash/levellist.php");

        $query = "SELECT * FROM t_levelrecords WHERE user_id = :userid AND progress > 99 AND sart = '" . strtoupper($this->viewMode) . "'";
        $DB->query($query, ['userid' => SESS_USERID]);
        $records = $DB->RSArray;

        $querytime = timer_end("LEVEL_query");

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

            $templateKeys = [];

            if($this->viewMode == "aredl") {
                $templateKeys['verifier'] = $level['verifier'];
            }

            $Template->load_hash(array_merge([
                "LEVELNAME" => $level['name'],
                "LEVELNAME_RAW" => $level['raw_name'],
                "PLACEMENT" => $level['position'],
                "CREATOR" => $level['creator'],
                "ID" => $levelKey,
                // "THUMBNAIL" => "https://levelthumbs.prevter.me/thumbnail/" . $levelKey . "/small",
                "COUNTER" => $counter,
                "LEVELID" => $levelKey,
                "COMPLETED" => ($found ? "completed" : ""),
                "BUTTONTEXT" => ($found ? "uncheck" : "check"),
                "ATTEMPTS" => $attempts,
                "LEVEL_LINK" => $level_url,
            ], $templateKeys));

            $Template->compile_template();
            $list .= $Template->get_output();
        }

        $pageLinks = '';
        $range = ($ges_pages < 3 ? $ges_pages : 3);

        if($this->viewMode == "aredl") {
            $urltype = "extremedemons";
        } else {
            $urltype = "ninecircles";
        }

        for ($i = max(1, $page - $range); $i <= min($ges_pages, $page + $range); $i++) {
            if ($i == $page) {
                $pageLinks .= '<span class="page-link active">' . $i . '</span>';
            } else {
                $params = array_filter([
                    'q' => INS['q'] ?? null,
                    'checked' => $isChecked ? 'true' : null,
                    'uncompleted' => $checkedUncompleted ? 'true' : null,
                    'page' => $i
                ]);
                
                $queryString = '?' . http_build_query($params);
                
                $pageLinks .= '<a href="/geometrydash/' . $urltype . $queryString . '" class="page-link">'.$i.'</a>';
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

        $query = "SELECT * from t_" . $this->viewMode . " order by position asc fetch first 3 rows only";
        $DB->query($query, []);

        $top3 = "";

        for($i = 0; $i < $DB->rows; $i++) {
            $top3 .= $DB->RSArray[$i]['name'] . ($i == 2 ? "" : ", ");
        }

        if($this->viewMode == "aredl") {
            set_meta_tags("View the current Ranking of $ALLlevelcount Extreme Demons and track your progress! The Current top 3 hardest Levels are: $top3", "description");
            $url_listtype = "extremedemons";
        } else {
            set_meta_tags("View the current Ranking of $ALLlevelcount Nine Circles List Demons and track your progress! The Current top 3 hardest Levels are: $top3", "description");
            $url_listtype = "ninecircles";
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
            "COMPLETED" => (($isChecked ?? false) ? "completed" : ""),
            "CHECKED_UNCOMPLETED" => ($checkedUncompleted ? "true" : "false"),
            "UNCOMPLETED_FILTER" => (($checkedUncompleted ?? false) ? "completed" : ""),
            "LEVELCOUNT" => $ALLlevelcount,
            "QUERYTIME" => $querytime,
            "URL_LISTTYPE" => $url_listtype,
            "SART" => strtoupper($this->viewMode)
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
 
        $Level = new Level($levelid, ($this->viewMode == "aredl" ? ListArt::AREDL : ListArt::NCL));

        if(!$Level->exists()) {
            $this->level_not_found($levelid);
        }

        if($this->viewMode == "aredl") {
            set_title("Extreme Demon List - " . $Level->name());
        } else {
            set_title("Nine Circles Demon List - " . $Level->name());
        }

        load_css("aredl");

        $Template->load_template("/geometrydash/level_detail.php");
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
        if($this->viewMode == "aredl") {
            set_title("Extreme Demon List");
        } else {
            set_title("Nine Circles Demon List");
        }

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

        if($this->viewMode == "aredl") {
            $urltype = "extremedemons";
        } else {
            $urltype = "ninecircles";
        }

        $level_url = "/geometrydash/$urltype/"; // maybe in component auslagern
        $level_url .= str_replace("_", "-", $levelname);
        $level_url .= "-" . $levelid;

        return $level_url;
    }
}