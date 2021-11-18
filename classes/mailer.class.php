<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer helper class
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class Mailer
{
    /**
     * Send email
     * @param string $to recipients address
     * @param string $subject the email subject
     * @param string $message the email body
     * @param string $from sender address
     * @param string $reply reply-to address
     * @param string $cc cc address
     * @param string $bcc bcc address
     * @param bool $html flag to indicate if html email or not
     */
    public static function send(
        string $to,
        string $subject,
        string $message,
        string $from = '',
        string $reply = '',
        string $cc = '',
        string $bcc = '',
        bool $html = false
    ): bool {
        $configObject = Config::get_instance();
        if ($configObject->is_behat_site() or $configObject->is_phpunit_site()) {
            // Fake email sending in automated tests.
            return true;
        }
        $mail = new PHPMailer(true);
        try {
            if (empty($configObject->get_setting('core', 'mailer_host'))) {
                $mail->isMail();
            } else {
                // Server settings.
                $mail->SMTPDebug = $configObject->get_setting('core', 'mailer_debug');
                $mail->isSMTP();
                $mail->Host = $configObject->get_setting('core', 'mailer_host');
                $mail->SMTPAuth = $configObject->get_setting('core', 'mailer_auth');
                if ($mail->SMTPAuth) {
                    $mail->Username = $configObject->get_setting('core', 'mailer_username');
                    $mail->Password = $configObject->get_setting('core', 'mailer_password');
                }
                $mail->SMTPSecure = $configObject->get_setting('core', 'mailer_secure');
                $mail->Port = $configObject->get_setting('core', 'mailer_port');
            }
            // Recipients.
            if ($from == '') {
                $from = support::get_primary_email();
            }
            if ($reply == '') {
                $reply = support::get_primary_email();
            }
            $mail->CharSet = $mail::CHARSET_UTF8;
            $mail->setFrom($from);
            $mail->addAddress($to);
            $mail->addReplyTo($reply);
            if ($cc != '') {
                $mail->addCC($cc);
            }
            if ($bcc != '') {
                $mail->addBCC($bcc);
            }
            // Content.
            if ($html) {
                $mail->isHTML($html);
            }
            $mail->Subject = $subject;
            $mail->Body    = $message;
            // Send.
            $mail->send();
        } catch (Exception $e) {
            $log = new Logger($configObject->db);
            $userObj = \UserObject::get_instance();
            if (!is_null($userObj)) {
                $userid = $userObj->get_user_ID();
                $username = $userObj->get_username();
            } else {
                $userid = 0;
                $username = '';
            }
            $errorfile = __FILE__;
            $errorline = __LINE__ - 11;
            $log->record_application_warning($userid, $username, $mail->ErrorInfo, $errorfile, $errorline);
            return false;
        }
        return true;
    }
}
