<?php
class Account implements Routable {
    private $mode = "";
    public function __construct($mode) {
        $this->mode = $mode['mode'];
    }
    public function init() {
        match ($this->mode) {
            "register"     => $this->register(),
            "create_new"   => $this->start_register(INS),
            "login"        => $this->login(),
            "init"         => $this->start_login(INS),
            "verify"       => $this->start_verification(),
            "verify_code"  => $this->check_verification_code(INS),
            "edit"         => $this->editAccountPage(INS),
            default        => null,
        };
    }
    private function register() {
        $template = new Template();

        $template->load_template("account/create.php");
        $template->load_hash([
            "URL_HTBASE" => URL_HTBASE,
            "ERROR"      => INS['message'] ?? '',
            "DISP_ERROR" => (!empty(INS['message']) ? '' : 'hidden')
        ]);
        $template->compile_template();
        $template->show_template();
        load_css("account_create");
        set_title("Create Account");
    }
    private function login() {
        $template = new Template();

        if(SESS_USERID != -1) {
            header("Location: " . URL_HTBASE);
        }

        $template->load_template("account/login.php");
        $template->load_hash([
            "URL_HTBASE" => URL_HTBASE,
            "ERROR"      => INS['message'] ?? '',
            "DISP_ERROR" => (!empty(INS['message']) ? '' : 'hidden')
        ]);
        $template->compile_template();
        $template->show_template();
        load_css("account_login");
        set_title("Login");
    }
    private function start_register($inHash) {
        $DB = new Database();

        $error = null;

        $requiredFields = ['username', 'email', 'password', 'password2'];
        foreach ($requiredFields as $field) {
            if (empty($inHash[$field])) {
                $error = "Missing field: " . $field;
                break;
            }
        }

        if(strlen($inHash['username']) > 20) {
            $error = "Username too long!";

        } else if(strlen($inHash['email']) > 100) {
            $error = "Email too long!";

        } else if(strlen($inHash['password']) > 100) {
            $error = "Password too long!";

        } else if($inHash['password'] != $inHash['password2']) {
            $error = "Passwords are not matching!";
        }

        if($error != 0) {
            header("Location: " . URL_HTBASE . "account/create?message=" . urlencode($error));
            die;
        }

        $password = password_hash($inHash['password'], PASSWORD_DEFAULT);

        $query = "BEGIN :result := manage_email_ver.generate_code(:username, :email, :password); END;";
        $binds = [
            "username" => $inHash['username'],
            "email" => $inHash['email'],
            "password" => $password
        ];

        $DB->callFunctionToRS($query, $binds, "result", SQLT_CHR);
        $result = strtolower($DB->RSArray['result']);

        if((int)$result == 101) {
            $error = "Username already taken!";

        } else if((int)$result == 102) {
            $error = "There's already an account with that Email!";

        } else if((int)$result == 0) {
            // All Ok

            session_start();
            $_SESSION['transfer_verify'] = array_merge(INS, ["verify_code" => $result]);

            header("Location: /account/verify");
            die;
        } else {
            $error = "Special characters are not allowed!";
        }

        if($error != 0) {
            header("Location: " . URL_HTBASE . "account/create?message=" . urlencode($error));
            die;
        }
    }
    private function start_login($inHash) {
        $DB = new Database();

        if(SESS_USERID != -1) {
            header("Location: " . URL_HTBASE);
        }

        $error = null;

        $requiredFields = ['username', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($inHash[$field])) {
                $error = $field . " cannot be empty!";
                break;
            }
        }

        $query = "SELECT * from t_users where (username = :username or email = :email)";
        $binds = [
            "username" => $inHash['username'],
            "email" => $inHash['username']
        ];

        $DB->query($query, $binds);

        if(empty($DB->RSArray[0]['userid'])) {
            $error = "Account not found!";
        }

        if(empty($DB->RSArray[0]['email'])) {
            $error = "Account not found!";
        }

        if($error != null) {
            header("Location: " . URL_HTBASE . "account/login?message=" . urlencode($error));
            die;
        }

        if(password_verify($inHash['password'], $DB->RSArray[0]['password'])) {
            // Success
            $sess = new Session();
            $sess->create_session($DB->RSArray[0]['userid']);

            if (isset($inHash['remember_me']) && (int)$inHash['remember_me'] === 1) {
                $sess->create_persistent_session($DB->RSArray[0]['userid']);
            }

            header("Location: " . URL_HTBASE . "?message=" . urlencode("Successfully logged in!"));
            die;

        } else {
            header("Location: " . URL_HTBASE . "account/login?message=" . urlencode("Wrong password!"));
            die;
        }
    }
    private function start_verification() {
        session_start();

        $inHash = $_SESSION['transfer_verify'];
        session_destroy();

        $Template = new Template();
        $Mail = new Mail($inHash['email']);

        $verify_code = $inHash['verify_code'];

        $Template->load_template("account/verify_mail.php");
        $Template->load_hash([
            "URL_HTBASE" => URL_HTBASE,
            "CODE" => $verify_code
        ]);
        $Template->compile_template();

        $mail_html = $Template->get_output();

        $Mail->setSubject("potzplitz.de - Verify your account");
        $Mail->setMessage($mail_html);

        $Mail->send();

        $Template->load_template("account/verify.php");
        $Template->load_hash([
            "EMAIL" => $inHash['email']
        ]);
        $Template->compile_template();
        $Template->show_template();
    }
    private function check_verification_code($inHash) {
        $DB = new Database();

        $query = "SELECT * from t_verifi where code = :code";
        $binds = [
            "code" => $inHash['token']
        ];

        $DB->query($query, $binds);

        if($DB->rows > 0) {

            $query = "BEGIN :result := manage_data.create_User(:username, :email, :password); END;";
            $binds = [
                "username" => $DB->RSArray[0]['username'],
                "email"    => $DB->RSArray[0]['email'],
                "password" => $DB->RSArray[0]['password']
            ];

            $DB->callFunctionToRS($query, $binds, "result", SQLT_CHR);

            $result = strtolower($DB->RSArray['result']);

            if((int)$result == 101) {
                $error = "Username already taken!";

            } else if((int)$result == 102) {
                $error = "There's already an account with that Email!";
            }

            if($error != 0) {
                header("Location: " . URL_HTBASE . "?message=" . urlencode($error));
                die;
            }

            $query = "DELETE from t_verifi where code = :code";
            $binds = [
                "code" => $inHash['token']
            ];

            $DB->query($query, $binds);

            header("Location: " . URL_HTBASE . "?message=" . urlencode("Account creation successful! you can login now."));

        } else {
            echo "Verification code is invalid or expired!";
            die;
        }
    }
    private function editAccountPage() {
        $DB = new Database();
        $Template = new Template();

        $User = new User(SESS_USERID);
        $User->load_user();

        if(!$User->isLoggedIn()) {
            echo "<h2>You have to be logged in to do that!</h2>";
            return;
        }

        set_title("Edit Account");

        $Template->load_template("account/edit.php");
        $Template->compile_template();
        $Template->show_template();
    }
}
