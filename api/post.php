<?php
require_once '../include/functions.php';
sec_session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET["action"])) {
    $res = new stdClass();

    if ($_GET["action"] == "login") {
        if (!login_check()) {
            if (isset($_POST['username'], $_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];
                if (login($username, $password)) {
                    $res->success = true;
                    echo json_encode($res);
                    return;
                } else {
                    $res->success = false;
                    $res->msg     = "Användarnamnet eller lösenordet var fel, försök igen!";
                    echo json_encode($res);
                    return;
                }
            } else {
                badRequest();
                return;
            }
        } else {
            $res->success = true;
            echo json_encode($res);
            return;
        }

        echo json_encode($res);
        return;
    } elseif ($_GET["action"] == "vote") {
        if (login_check()) {
            if (isset($_POST['choice'], $_POST['poll_id'], $_POST['csrf_token'])) {
                if (ctype_digit($_POST["choice"]) && ctype_digit($_POST["poll_id"])) {
                    $choice_id  = $_POST['choice'];
                    $poll_id    = $_POST['poll_id'];
                    $csrf_token = $_POST['csrf_token'];
                    $csrf       = new csrf();

                    if (!$csrf->checkValid("post")) {
                        badRequest();
                        return;
                    }

                    if (!pollExists($poll_id)) {
                        badRequest();
                        return;
                    }

                    if (!userCanVoteToday($poll_id)) {
                        $res->success = false;
                        $res->msg     = "Du har redan röstat idag!";
                        echo json_encode($res);
                        return;
                    }

                    if (!choiceExistsForPoll($choice_id, $poll_id)) {
                        badRequest();
                        return;
                    }

                    if (registerVote($poll_id, $choice_id, getActiveUserId())) {
                        $res->success = true;
                        echo json_encode($res);
                        return;
                    } else {
                        $res->success = false;
                        $res->msg     = "Något gick fel, föksök igen!";
                        echo json_encode($res);
                        return;
                    }
                } else {
                    badRequest();
                    return;
                }
            }
        } else {
            $res->success = true;
            echo json_encode($res);
            return;
        }

        echo json_encode($res);
        return;
    } elseif ($_GET["action"] == "changepassword") {
        if (login_check()) {
            if (isset($_POST['oldpw'], $_POST['newpw1'], $_POST['newpw2'], $_POST['csrf_token'])) {
                $oldpw      = $_POST['oldpw'];
                $newpw1     = $_POST['newpw1'];
                $newpw2     = $_POST['newpw2'];
                $csrf_token = $_POST['csrf_token'];
                $csrf       = new csrf();

                if (!$csrf->checkValid("post")) {
                    badRequest();
                    return;
                }

                if (!passwordIsCorrectForCurrentUser($oldpw)) {
                    $res->success = false;
                    $res->msg     = "Felaktigt gammalt lösenord!";
                    echo json_encode($res);
                    return;
                }

                if ($newpw1 !== $newpw2) {
                    $res->success = false;
                    $res->msg     = "Ditt nya lösenord matchar inte i båda rutorna!";
                    echo json_encode($res);
                    return;
                }

                if (!passwordIsCorrectlyFormatted($newpw1)) {
                    $res->success = false;
                    $res->msg     = "Ditt nya lösenord innehåller ett eller flera otillåtna tecken!";
                    echo json_encode($res);
                    return;
                }

                if (changePasswordForCurrentUser($newpw1)) {
                    $res->success = true;
                    $res->msg     = "Ditt lösenord har uppdaterats!";
                    echo json_encode($res);
                    return;
                } else {
                    $res->success = false;
                    $res->msg     = "Något gick fel, föksök igen!";
                    echo json_encode($res);
                    return;
                }
            }
        } else {
            $res->success = true;
            echo json_encode($res);
            return;
        }

        echo json_encode($res);
        return;
    } elseif ($_GET["action"] == "createlink") {
        if (login_check() && activeUserIsAdmin()) {
            $csrf_token = $_POST['csrf_token'];
            $csrf       = new csrf();

            if (!$csrf->checkValid("post")) {
                badRequest();
                return;
            }

            if (createRegistrationLink()) {
                $res->success = true;
                echo json_encode($res);
                return;
            } else {
                $res->success = false;
                $res->msg     = "Något gick fel, föksök igen!";
                echo json_encode($res);
                return;
            }
        } else {
            $res->success = true;
            echo json_encode($res);
            return;
        }

        echo json_encode($res);
        return;
    } elseif ($_GET["action"] == "register") {
        if (!login_check()) {
            if (isset($_POST['username'], $_POST['newpw1'], $_POST['newpw2'], $_POST['register_token'], $_POST['csrf_token'])) {
                $username       = $_POST['username'];
                $newpw1         = $_POST['newpw1'];
                $newpw2         = $_POST['newpw2'];
                $register_token = $_POST['register_token'];
                $csrf_token     = $_POST['csrf_token'];
                $csrf           = new csrf();

                if (!$csrf->checkValid("post")) {
                    badRequest();
                    return;
                }

                if (!tokenIsValid($register_token)) {
                    badRequest();
                    return;
                }

                if (userNameIsTaken($username)) {
                    $res->success = false;
                    $res->msg     = "Ditt valda användarnamn är upptaget. Välj ett nytt!";
                    echo json_encode($res);
                    return;
                }

                if (!usernameIsCorrectlyFormatted($username)) {
                    $res->success = false;
                    $res->msg     = "Ditt användarnamn innehåller ett eller flera otillåtna tecken!";
                    echo json_encode($res);
                    return;
                }

                if ($newpw1 !== $newpw2) {
                    $res->success = false;
                    $res->msg     = "Ditt lösenord matchar inte i båda rutorna!";
                    echo json_encode($res);
                    return;
                }

                if (!passwordIsCorrectlyFormatted($newpw1)) {
                    $res->success = false;
                    $res->msg     = "Ditt lösenord innehåller ett eller flera otillåtna tecken!";
                    echo json_encode($res);
                    return;
                }

                if (registerAccount($username, $newpw1)) {
                    consumeRegisterToken($register_token);
                    login($username, $newpw1);
                    $res->success = true;
                    echo json_encode($res);
                    return;
                } else {
                    $res->success = false;
                    $res->msg     = "Något gick fel, föksök igen!";
                    echo json_encode($res);
                    return;
                }
            }
        } else {
            $res->success = true;
            echo json_encode($res);
            return;
        }

        echo json_encode($res);
        return;
    }
} else {
    badRequest();
    return;
}
