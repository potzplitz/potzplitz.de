<?php
class ErrorCodeHandler {
    private int $errorcode = 500;
    private string $redirecturl = "/";
    private bool $autoredirect = false;
    private bool $exitAfterHandle = false;
    private const HTTP_STATUS_TEXT = [
        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',

        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];
    public function setErrorCode(int $code) {
        $this->errorcode = $code;
    }

    public function setRedirect(string $url, bool $autoredirect) { // If $autoredirect == true then errorpage will not be displayed and automatically redirected to given URL
        $this->redirecturl = $url;
        $this->autoredirect = $autoredirect;
    }

    public function handle(bool $exitAfter = false): int {
        $this->exitAfterHandle = $exitAfter;

        $this->checkErrorcodeExists();
    
        if($this->autoredirect) {
            return $this->redirectToURL();

        } else {
            return $this->showErrorPage();
        }
    }

    private function checkExitAfterHandle() {
        if($this->exitAfterHandle) {
            exit();
        }
    }

    private function redirectToURL(): int {
        header("Location: " . $this->redirecturl, true, 302);
        $this->checkExitAfterHandle();
        return 0;
    }

    private function showErrorPage() {
        http_response_code($this->errorcode);
        // error page logic
        
        $Template = new Template();
        $Template->load_template("general/http_error.php");
        $Template->load_hash([
            "ERROR_CODE" => $this->errorcode,
            "ERROR_DESCRIPTION" => self::HTTP_STATUS_TEXT[$this->errorcode],
            "REDIRECT_URL" => $this->redirecturl
        ]);
        $Template->show_template();

        $this->checkExitAfterHandle();
        return 0;
    }

    private function checkErrorcodeExists() {
        if(!array_key_exists($this->errorcode, self::HTTP_STATUS_TEXT)) {
            debug_mail("Invalid HTTP Status Code! User ID: " . SESS_USERID . " | Route: " . ROUTE . " | Status Code: " . $this->errorcode);
            throw new InvalidArgumentException("Invalid HTTP Status Code!");
        }
    }
}