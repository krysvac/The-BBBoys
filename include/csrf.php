<?php
define("TOKEN_NAME", "csrf_token");

class csrf
{
    public function init()
    {
        if (!isset($_SESSION[TOKEN_NAME])) {
            $token                = hash('sha256', $this->random(500));
            $_SESSION[TOKEN_NAME] = $token;
        }
    }

    public function getToken()
    {
        if (!isset($_SESSION[TOKEN_NAME])) {
            init();
        }
        return $_SESSION[TOKEN_NAME];
    }

    public function checkValid($method)
    {
        if ($method == 'post') {
            if (isset($_POST[TOKEN_NAME]) && hash_equals($this->getToken(), $_POST[TOKEN_NAME])) {
                return true;
            }
        } else {
            if (isset($_GET[TOKEN_NAME]) && hash_equals($this->getToken(), $_GET[TOKEN_NAME])) {
                return true;
            }
        }

        return false;

    }

    public function random($len)
    {
        $return = "";
        if (function_exists('openssl_random_pseudo_bytes')) {
            $byteLen = intval(($len / 2) + 1);
            $return  = substr(bin2hex(openssl_random_pseudo_bytes($byteLen)), 0, $len);
        } elseif (@is_readable('/dev/urandom')) {
            $f       = fopen('/dev/urandom', 'r');
            $urandom = fread($f, $len);
            fclose($f);
            $return = '';
        }

        if (empty($return)) {
            for ($i = 0; $i < $len; ++$i) {
                if (!isset($urandom)) {
                    if ($i % 2 == 0) {
                        mt_srand(time() % 2147 * 1000000 + (double) microtime() * 1000000);
                    }
                    $rand = 48 + mt_rand() % 64;
                } else {
                    $rand = 48 + ord($urandom[$i]) % 64;
                }

                if ($rand > 57) {
                    $rand += 7;
                }

                if ($rand > 90) {
                    $rand += 6;
                }

                if ($rand == 123) {
                    $rand = 52;
                }

                if ($rand == 124) {
                    $rand = 53;
                }

                $return .= chr($rand);
            }
        }
        return $return;
    }
}
