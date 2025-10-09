<?php

class StaticFiles {
    private $baseDir;

    public function __construct($baseDir = __DIR__ . "/../static") {
        $this->baseDir = realpath($baseDir);
    }

    public function serve(string $request): bool {
        $allowed = [
            "/favicon.ico" => "image/x-icon",
            "/robots.txt"  => "text/plain",
        ];

        if (!isset($allowed[$request])) {
            return false;
        }

        $filePath = $this->baseDir . $request;

        if ($this->isSafePath($filePath) && file_exists($filePath)) {
            header("Content-Type: " . $allowed[$request]);
            header("Content-Length: " . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            header("HTTP/1.1 204 No Content");
            exit;
        }
    }

    private function isSafePath(string $path): bool {
        return strpos(realpath($path), $this->baseDir) === 0;
    }
}
