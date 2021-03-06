<?php
/**
 * Verifies a user's account.
 *
 * PHP version 5.5.38
 *
 * @category  Action
 * @package   Camagru
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   No License
 * @link      localhost:8080
 */
session_start();
require_once '../config/paths.php';
require_once '../config/connect.php';
require_once '../includes/lib/auth.php';
require_once '../includes/models/User.php';
require_once '../includes/models/Like.php';

headerStatus(200);
$Like = new Like($dbh);
$User = new User($dbh);

try {
    if (isset($_GET["code"])) {
        $code = urldecode($_GET["code"]);
        $status = $User->verify($code);

        if ($status == User::$ALREADY_VERIFIED) {
            $_SESSION["message"] = '<h3 class="err-msg">This email has already be verified.</h3>';
            header("Location: ../pages/message.php");
            exit(0);
        } else if ($status == User::$VERIFIED_FAILED) {
            $_SESSION["message"] = '<h3 class="err-msg">Failed to verify email. Please contact Akia Vongdara (vongdarakia@gmail.com) for support.</h3>';
            header("Location: ../pages/message.php");
            exit(0);
        }

        $query = "select first, last, email, username from `user` u
            inner join `email_confirmation` ec on ec.author_id = u.id
            where ec.`code`=:code";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array(":code" => $code));
        $user = $stmt->fetchObject();

        $url = WEBSITE_HOME_URL;
        $sub = "Camagru: Your email have been verified!";
        $msg = "Your email have been verified! Now you can log into your account!"
            . "!<br><br>Your username is " . $user->username
            . "<br><br>We hope you enjoy the <a href='" . $url . "'>app</a>!";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        mail($user->email, $sub, $msg, $headers);

        $_SESSION["message"] = '<h2 class="thin"> Welcome to Camagru, '. $user->first
        .'!</h2> <p class="thin">Your email have been verified! Now you can log into your account!</p>';
        header("Location: ../pages/message.php");
        exit(0);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 200);
}
?>