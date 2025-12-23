<?php 
class Sitemap implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        switch($this->mode) {
            case "sitemap":
                $this->generate_sitemap();
            break;
        }
    }
    private function generate_sitemap() {
        header("Content-Type: application/xml; charset=utf-8");

        $DB = new Database();

        $DB->query("SELECT route FROM routes WHERE indexable = 1 AND route NOT LIKE '%{%'", []);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $base = "https://potzplitz.de";

        foreach ($DB->RSArray as $row) {
            $url = $base . $row['route'];
            echo '<url>';
            echo '<loc>' . htmlspecialchars($url) . '</loc>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
        exit;
    }
}