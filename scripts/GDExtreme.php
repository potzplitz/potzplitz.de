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

        $querycount = "SELECT COUNT(*) as cnt FROM t_aredl";
        $query = "SELECT * FROM t_aredl";
        $bindsCount = [];
        $binds = [];

        $whereParts = [];

        if (!empty(INS['q'])) {
            $q = trim(strtolower(INS['q']));

            if (ctype_digit($q)) {
                $whereParts[] = "id = :idquery";
                $bindsCount['idquery'] = $q;
                $binds['idquery'] = $q;
            }

            $whereParts[] = "LOWER(name) LIKE :query";
            $whereParts[] = "LOWER(raw_name) LIKE :query";
            $bindsCount['query'] = "%{$q}%";
            $binds['query'] = "%{$q}%";
        }

        if ($whereParts) {
            $where = " WHERE " . implode(" OR ", $whereParts);
            $querycount .= $where;
            $query .= $where;
        }

        $DB2->query($querycount, $bindsCount);
        $row = $DB2->RSArray[0];
        $ges_pages = ceil($row['cnt'] / 100);

        $page = (INS['page'] ?? 1);
        $page = max(1, min($page, $ges_pages ?: 1));

        $per_page = 100;
        $offset = ($page - 1) * $per_page;

        $query .= " ORDER BY position ASC OFFSET :offset ROWS FETCH FIRST 100 ROWS ONLY";
        $binds['offset'] = $offset;

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

        // --- Pagination-Links ---
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
            "QUERY" => INS['q'] ?? ''
        ]);

        $Template2->compile_template();
        $Template2->show_template();
    }

    private function show_detail($levelid) {
        // TODO Router so umbauen dass er mit dynamischen /{id}/... routen umgehen kann
    }
}