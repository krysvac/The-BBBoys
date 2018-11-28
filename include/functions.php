<?php
error_reporting(0);
define("DEVELOPMENT", ($_SERVER['HTTP_HOST'] == 'localhost') ? true : false);

require_once "csrf.php";
require_once "database.php";

// ********************************
// **
// ** SSL
// **
// ********************************
define("SSL_CONNECTION", DEVELOPMENT ? false : true);

define("HTTP_ONLY", true);

define("INCATIVE_TIME_SECONDS", 1800);

define("SESSION_NAME", "thebbboys_matinfo");

function connect()
{
    $db_dsn  = "mysql:host=" . DBHOST . ";dbname=" . DBNAME;
    $db_user = DBUSERNAME;
    $db_pass = DBPASSWORD;
    $pdo     = new PDO($db_dsn, $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
    return $pdo;
}

function sec_session_start()
{
    date_default_timezone_set("Europe/Stockholm");

    $session_name = SESSION_NAME; // Set a custom session name
    $secure       = SSL_CONNECTION;
    $httponly     = HTTP_ONLY; // This stops JavaScript being able to access the session id.
    $inactive     = INCATIVE_TIME_SECONDS;

    ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
    ini_set('session.gc_maxlifetime', $inactive); // set the session max lifetime

    if (ini_set('session.use_only_cookies', 1) === false) {
        header('HTTP/1.1 500 Internal Server Error', false, 500);
        die();
        exit();
    }

    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly);

    // Sets the session name to the one set above.
    session_name($session_name);
    session_start(); // Start the PHP session
    session_regenerate_id(); // regenerated the session, delete the old one.

    $csrf = new csrf();
    $csrf->init();
}

function login($username, $password)
{
    $pdo = connect();
    // Using prepared statements means that SQL injection is not possible.
    if ($stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username=:uname LIMIT 1")) {
        $stmt->execute(array(":uname" => $username));
        $res = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$res) {
            // Username does not exist
            return false;
        } else {
            // Check if the password in the database matches
            // the password the user submitted.
            if (password_verify($password, $res->password)) {
                // Password is correct!
                // Get the user-agent string of the user.
                $userBrowser = $_SERVER['HTTP_USER_AGENT'];

                // XSS protection as we might print this value
                $userId = preg_replace("/[^0-9]+/", "", $res->id);

                $_SESSION['user_ID'] = $userId;

                // XSS protection as we might print this value
                $username                 = preg_replace("/[^a-zA-ZÅåÄäÖö0-9_\-]+/", "", $username);
                $_SESSION['username']     = $username;
                $_SESSION['login_string'] = hash('sha512', $res->password . $userBrowser);
                $_SESSION['afk']          = time();

                // Login successful.
                return true;
            }
        }
    }
}

function login_check()
{
    $pdo = connect();
    // Check if all session variables are set
    if (isset($_SESSION['user_ID'], $_SESSION['username'], $_SESSION['login_string'])) {
        $userId      = $_SESSION['user_ID'];
        $loginString = $_SESSION['login_string'];

        // Get the user-agent string of the user.
        $userBrowser = $_SERVER['HTTP_USER_AGENT'];

        if ($stmt = $pdo->prepare("SELECT password FROM users WHERE id=:id LIMIT 1")) {
            $stmt->execute(array(":id" => $userId));
            $res = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$res) {
                // User does not exist
                return false;
            } else {
                // If the user exists get variables from result.
                $loginCheck = hash('sha512', $res->password . $userBrowser);

                if ($loginCheck == $loginString) {
                    $inactive = 3600;
                    if (time() - $_SESSION['afk'] > $inactive) {
                        logout();
                    } else {
                        $_SESSION['afk'] = time();
                        // Logged In
                        return true;
                    }
                } else {
                    logout();
                }
            }
        } else {
            // Not logged in
            return false;
        }
    } else {
        // Not logged in
        return false;
    }
}

function logout()
{
    // Unset all session values
    $_SESSION = array();

    // get session parameters
    $params = session_get_cookie_params();

    // Delete the actual cookie.
    setcookie(
        session_name(),
        '',
        time() - INCATIVE_TIME_SECONDS,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

    // Destroy session
    session_destroy();

    header('HTTP/1.1 403 Forbidden', false, 403);
    header('Location: /login.php');
    exit();
}

function convertDay($day)
{
    $daysSwe = array(
        "monday"    => "Måndag",
        "tuesday"   => "Tisdag",
        "wednesday" => "Onsdag",
        "thursday"  => "Torsdag",
        "friday"    => "Fredag");

    return $daysSwe[$day];
}

function getTimeoutItems()
{
    $days = array(
        "monday"    => "<p><strong>Måndag",
        "tuesday"   => "<p><strong>Tisdag",
        "wednesday" => "<p><strong>Onsdag",
        "thursday"  => "<p><strong>Torsdag",
        "friday"    => "<p><strong>Fredag");

    $daysEndings = array(
        "monday"    => "</strong>",
        "tuesday"   => "</strong>",
        "wednesday" => "<br />",
        "thursday"  => "<br />",
        "friday"    => "<br />");

    $website_url = "http://www.timeoutlyckeby.se/dagens-ratt/";
    $curl        = curl_init();
    curl_setopt($curl, CURLOPT_URL, $website_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($curl);
    curl_close($curl);

    $items = new stdClass();

    foreach ($days as $key => $value) {
        $start       = strpos($html, $value);
        $end         = strpos($html, '</p>', $start);
        $length      = $end - $start;
        $items->$key = substr($html, $start, $length);
        $items->$key = strstr($items->$key, $daysEndings[$key]);
        $items->$key = substr($items->$key, strlen($daysEndings[$key]));
    }

    return $items;
}

function getBistroJItems()
{
    $days = array(
        "monday"    => '<th class="amatic-700 whoa text-danger">M',
        "tuesday"   => '<th class="amatic-700 whoa text-danger">Tis',
        "wednesday" => '<th class="amatic-700 whoa text-danger">Ons',
        "thursday"  => '<th class="amatic-700 whoa text-danger">Tors',
        "friday"    => '<th class="amatic-700 whoa text-danger">Fre');

    $mealStart = array(
        "monday"    => '<td width="33%">',
        "tuesday"   => '<td width="33%">',
        "wednesday" => '<td width="33%">',
        "thursday"  => '<td width="33%">',
        "friday"    => '<td width="33%">');

    $daysEndings = array(
        "monday"    => "</th>",
        "tuesday"   => "</th>",
        "wednesday" => "</th>",
        "thursday"  => "</th>",
        "friday"    => "</th>");

    $website_url = "http://www.hors.se/veckans-meny/?rest=183";
    $curl        = curl_init();
    curl_setopt($curl, CURLOPT_URL, $website_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($curl);
    curl_close($curl);

    $start_point = strpos($html, '<table id="mattabellen" class="table non-responsive-menu-table">');
    $end_point   = strpos($html, '</table>', $start_point);
    $length      = $end_point - $start_point;
    $html        = substr($html, $start_point, $length);

    $items = new stdClass();

    foreach ($days as $key => $value) {
        $start  = strpos($html, $value);
        $end    = strpos($html, '</tr>', $start);
        $length = $end - $start;
        $var    = substr($html, $start, $length);
        $var    = strstr($var, $daysEndings[$key]);
        $var    = trim(substr($var, strlen($daysEndings[$key])));

        $meals = array();
        $abort = false;
        while (!$abort) {
            $start_point = strpos($var, '<td width="33%">');
            $end_point   = strpos($var, '</td>', $start_point);

            if ($start_point === false || $end_point === false) {
                $abort = true;
                break;
            }

            $length = $end_point - $start_point;

            $add = substr($var, $start_point, $length + 5);
            $add = substr($add, strlen($mealStart[$key]));
            $add = substr($add, 0, -5);

            if (strpos($add, '<br />') !== false) {
                $splitmeals = explode("<br />", $add);

                foreach ($splitmeals as $split) {
                    array_push($meals, trim($split));
                }
            } else {
                array_push($meals, $add);
            }

            $var = trim(substr($var, $length + 5));
        }

        $items->$key = $meals;
    }

    return $items;
}

function getVillaItems()
{
    $days = array(
        "monday"    => '<th class="amatic-700 whoa text-danger">M',
        "tuesday"   => '<th class="amatic-700 whoa text-danger">Tis',
        "wednesday" => '<th class="amatic-700 whoa text-danger">Ons',
        "thursday"  => '<th class="amatic-700 whoa text-danger">Tors',
        "friday"    => '<th class="amatic-700 whoa text-danger">Fre');

    $mealStart = array(
        "monday"    => '<td width="33%">',
        "tuesday"   => '<td width="33%">',
        "wednesday" => '<td width="33%">',
        "thursday"  => '<td width="33%">',
        "friday"    => '<td width="33%">');

    $daysEndings = array(
        "monday"    => "</th>",
        "tuesday"   => "</th>",
        "wednesday" => "</th>",
        "thursday"  => "</th>",
        "friday"    => "</th>");

    $website_url = "http://www.hors.se/veckans-meny/?rest=2881";
    $curl        = curl_init();
    curl_setopt($curl, CURLOPT_URL, $website_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($curl);
    curl_close($curl);

    $start_point = strpos($html, '<table id="mattabellen" class="table non-responsive-menu-table">');
    $end_point   = strpos($html, '</table>', $start_point);
    $length      = $end_point - $start_point;
    $html        = substr($html, $start_point, $length);

    $items = new stdClass();

    foreach ($days as $key => $value) {
        $start  = strpos($html, $value);
        $end    = strpos($html, '</tr>', $start);
        $length = $end - $start;
        $var    = substr($html, $start, $length);
        $var    = strstr($var, $daysEndings[$key]);
        $var    = trim(substr($var, strlen($daysEndings[$key])));

        $meals = array();
        $abort = false;
        while (!$abort) {
            $start_point = strpos($var, '<td width="33%">');
            $end_point   = strpos($var, '</td>', $start_point);

            if ($start_point === false || $end_point === false) {
                $abort = true;
                break;
            }

            $length = $end_point - $start_point;

            $add = substr($var, $start_point, $length + 5);
            $add = substr($add, strlen($mealStart[$key]));
            $add = substr($add, 0, -5);

            if (strpos($add, '<br />') !== false) {
                $splitmeals = explode("<br />", $add);

                foreach ($splitmeals as $split) {
                    array_push($meals, trim($split));
                }
            } else {
                array_push($meals, $add);
            }

            $var = trim(substr($var, $length + 5));
        }

        $items->$key = $meals;
    }

    return $items;
}

function currentDayClass($key)
{
    $days = array(
        'monday'    => 1,
        'tuesday'   => 2,
        'wednesday' => 3,
        'thursday'  => 4,
        'friday'    => 5);

    $currentday = date('w');

    if ($days[$key] == $currentday) {
        return "activeDay";
    } else {
        return "";
    }
}

function currentDayExpand($key)
{
    $days = array(
        'monday'    => 1,
        'tuesday'   => 2,
        'wednesday' => 3,
        'thursday'  => 4,
        'friday'    => 5);

    $currentday = date('w');

    if ($days[$key] == $currentday) {
        return "true";
    } else {
        return "false";
    }
}

function badRequest()
{
    header('HTTP/1.1 400 Bad Request', false, 400);
    exit();
}

function getAllPolls()
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls');
    $stmt->execute();

    if ($stmt->rowCount() >= 1) {
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $res;
    } else {
        return false;
    }
}

function getPollChoicesByPollId($id)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls_choices WHERE poll_id =:id');
    $stmt->execute(array(":id" => $id));

    if ($stmt->rowCount() >= 1) {
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $res;
    } else {
        return false;
    }
}

function getAnswersForPollByPollId($poll_id)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls_answers WHERE poll_id = :poll_id');
    $stmt->execute(array(":poll_id" => $poll_id));

    if ($stmt->rowCount() >= 1) {
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
        $ret = array();

        $start = strtotime('today midnight');
        $end   = strtotime('today midnight +23 hours 59 minutes 59 seconds');

        foreach ($res as $value) {
            $time = strtotime($value->timestamp);

            if ($time >= $start && $time <= $end) {
                array_push($ret, $value);
            }
        }

        return count($ret) > 0 ? $ret : false;

    } else {
        return false;
    }
}

function getDateSwe()
{
    $date = date("N");

    $daysTranslated = array(
        1 => "Måndag",
        2 => "Tisdag",
        3 => "Onsdag",
        4 => "Torsdag",
        5 => "Fredag",
        6 => "Lördag",
        7 => "Söndag");

    return $daysTranslated[intval($date)];
}

function pollExists($poll)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE id=:id');
    $stmt->execute(array('id' => $poll));

    if ($stmt->rowCount() >= 1) {
        return true;
    } else {
        return false;
    }
}

function choiceExistsForPoll($choice_id, $poll_id)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls_choices WHERE id=:choice_id AND poll_id=:poll_id');
    $stmt->execute(array(
        'choice_id' => $choice_id,
        "poll_id"   => $poll_id));

    if ($stmt->rowCount() >= 1) {
        return true;
    } else {
        return false;
    }
}

function userCanVoteToday($poll_id)
{
    $answers = getUsersAnswersForPoll(getActiveUserId(), $poll_id);

    return $answers === false ? true : false;
}

function registerVote($poll_id, $choice_id, $user_id)
{
    $pdo = connect();

    if (!pollExists($poll_id)) {
        return false;
    }

    if (!userCanVoteToday($poll_id)) {
        return false;
    }

    if (!choiceExistsForPoll($choice_id, $poll_id)) {
        return false;
    }

    $stmt = $pdo->prepare("INSERT INTO polls_answers (user_id, poll_id, choice_id, ip_address) VALUES (:user_id, :poll_id, :choice_id, :ip_address)");

    $res = $stmt->execute(array(
        ":user_id"    => $user_id,
        ":poll_id"    => $poll_id,
        ":choice_id"  => $choice_id,
        ":ip_address" => $_SERVER['REMOTE_ADDR']));

    return $res;
}

function getActiveUserId()
{
    if (isset($_SESSION)) {
        return $_SESSION['user_ID'];
    }

    return -1;
}

function getUsersAnswersForPoll($user_id, $poll_id)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM polls_answers WHERE user_id = :user_id AND poll_id = :poll_id');
    $stmt->execute(array(
        ":user_id" => $user_id,
        ":poll_id" => $poll_id));

    if ($stmt->rowCount() >= 1) {
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
        $ret = array();

        $start = strtotime('today midnight');
        $end   = strtotime('today midnight +23 hours 59 minutes 59 seconds');

        foreach ($res as $value) {
            $time = strtotime($value->timestamp);

            if ($time >= $start && $time <= $end) {
                array_push($ret, $value);
            }
        }

        return count($ret) > 0 ? $ret : false;

    } else {
        return false;
    }
}

function userHasVotedForChoice($poll_id, $choice_id)
{
    $answers = getUsersAnswersForPoll(getActiveUserId(), $poll_id);

    if ($answers === false) {
        return false;
    }

    foreach ($answers as $answer) {
        if ($answer->choice_id == $choice_id) {
            return true;
        }
    }

    return false;
}

function randomToken($length = 32)
{
    if (!isset($length) || intval($length) <= 8) {
        $length = 32;
    }
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

function getRegistrationLinks()
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM registration_links ORDER BY timestamp desc');
    $stmt->execute();

    if ($stmt->rowCount() >= 1) {
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $res;

    } else {
        return false;
    }
}

function passwordIsCorrectForCurrentUser($password)
{
    if (login_check()) {
        $pdo  = connect();
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE id=:id LIMIT 1");
        $stmt->execute(array(":id" => getActiveUserId()));

        if ($stmt->rowCount() === 1) {
            $res = $stmt->fetch(PDO::FETCH_OBJ);

            if (password_verify($password, $res->password)) {
                return true;
            }
        }
    }

    return false;
}

function changePasswordForCurrentUser($password)
{
    if (login_check()) {
        if (!passwordIsCorrectlyFormatted($password)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $pdo  = connect();
        $stmt = $pdo->prepare("UPDATE users SET password=:pw WHERE id=:id");
        $stmt->execute(array(
            ":pw" => $hash,
            ":id" => getActiveUserId()));

        $userBrowser              = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['login_string'] = hash('sha512', $hash . $userBrowser);
        $_SESSION['afk']          = time();

        return true;
    }
}

function passwordIsCorrectlyFormatted($password)
{
    if (!preg_match("/^[a-zA-ZåÅäÄöÖ\_0-9!@#.]+$/", $password)) {
        return false;
    }

    if (strlen($password) > 64 || strlen($password) < 10) {
        return false;
    }

    return true;
}

function usernameIsCorrectlyFormatted($password)
{
    if (!preg_match("/^[a-zA-ZåÅäÄöÖ\_0-9]+$/", $password)) {
        return false;
    }

    if (strlen($password) > 32 || strlen($password) < 2) {
        return false;
    }

    return true;
}

function activeUserIsAdmin()
{
    if (login_check()) {
        $pdo  = connect();
        $stmt = $pdo->prepare("SELECT isadmin FROM users WHERE id=:id LIMIT 1");
        $stmt->execute(array(":id" => getActiveUserId()));

        if ($stmt->rowCount() === 1) {
            $res = $stmt->fetch(PDO::FETCH_OBJ);

            if ($res->isadmin === "1") {
                return true;
            }
        }
    }

    return false;
}

function createRegistrationLink()
{
    $token = randomToken(32);

    $pdo  = connect();
    $stmt = $pdo->prepare("INSERT INTO registration_links (token) VALUES (:token)");
    $stmt->execute(array(":token" => $token));

    return true;
}

function tokenIsValid($token)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM registration_links where token=:token');
    $stmt->execute(array(":token" => $token));

    if ($stmt->rowCount() === 1) {
        $res = $stmt->fetch(PDO::FETCH_OBJ);

        if ($res->used === "0") {
            return true;
        }
    } else {
        return false;
    }
}

function userNameIsTaken($username)
{
    $pdo  = connect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username=:username');
    $stmt->execute(array('username' => $username));

    if ($stmt->rowCount() == 1) {
        return true;
    } else {
        return false;
    }
}

function registerAccount($username, $password)
{
    if (userNameIsTaken($username) || !usernameIsCorrectlyFormatted($username) || !passwordIsCorrectlyFormatted($password)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $pdo  = connect();
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");

    $stmt->execute(array(
        ':username' => $username,
        ':password' => $hash));

    if ($stmt->rowCount() == 1) {
        login($username, $password);
        return true;
    } else {
        return false;
    }
}

function consumeRegisterToken($token)
{
    $pdo  = connect();
    $stmt = $pdo->prepare("UPDATE registration_links SET used=:used WHERE token=:token");
    $stmt->execute(array(
        ":used"  => 1,
        ":token" => $token));

    return true;
}

function votingIsAllowed()
{
    $now = strtotime('now');
    $end = strtotime('today midnight +11 hours 30 minutes');

    if ($now < $end) {
        return true;
    }
    return false;
}
