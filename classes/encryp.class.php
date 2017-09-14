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

/**
 * Encryp package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @author John O'Rourke <john@o-rourke.org>
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 */

/**
 * Encryption helper class.
 */
class encryp {
    /**
     * Dictionary filename.
     * @var string 
     */
    private $file;
    /**
     * Array of dictionary words.
     * @var array 
     */
    private $dictionary;
    /**
     * Constructor
     */
    public function __construct() {
        $configObject = Config::get_instance();
        $this->file = $configObject->get_setting('core', 'misc_dictionary_file');
    }
    /**
     * Load dictionary into memory.
     */
    private function load() {
        // Revert to default password generation if no dictionary.
        if (!file_exists($this->file)) {
            $this->dictionary = array();
        } else {
            $words = array();
            $f = fopen($this->file, 'r');
            while (!feof($f)) {
                $word = fgets($f);
                if (preg_match('/^[a-z]{4,6}$/', trim($word))) {
                    $words[] = $word;
                }
            }
            fclose($f);
            // Revert to default password generation if dictionary too small.
            if (count($words) < 10000) {
              $this->dictionary = array();
            } else {
              $this->dictionary = $words;
            }
        }
    }
    /**
     * Encrypt a password that can be decrypted.
     * 
     * @param string $password 
     * @return string encrypted password
     */
    public function mcrypt_password($password) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, UserUtils::get_salt(), $password, MCRYPT_MODE_ECB, $iv);
        return trim(base64_encode($enc));
    }
    /**
    * Decrypt the password.
     * 
     * @param string $encpassword encrypted password
     * @return string decrypted password
     */
    public function mdecrypt_password($encpassword) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, UserUtils::get_salt(), base64_decode($encpassword), MCRYPT_MODE_ECB, $iv);
        return trim($dec);
    }
    /**
     * This is function encpw encrypts a password using SHA-512 for storage in the DB.
     * MD5 encryption is kept for backwards compatibility.
     *
     * @param string $salt the salt as set in the config.inc.php file
     * @param string $u username
     * @param string $p password
     * @param string $type the level of encryption to use
     * @return string encrypted password
     *
     */
    public function encpw($salt, $u, $p, $type = 'SHA-512') {
      if ($type == 'SHA-512') {
        $full_salt = '$6$' . $salt . '$'; // SHA-512
        $new_password = crypt($p, $full_salt);
        $new_password = '$6$' . substr($new_password, strlen($full_salt));
      } else {
        $full_salt = '$1$' . substr(md5($u), 0, 8) . '$'; // Simple MD5, for backwards compatibility
        $new_password = crypt($p, $full_salt);
      }

      return $new_password;
    }

    /**
     * Create a password that either relatively secure, but easy to read and dictate regardless of fonts, eyesight etc
     * or a random string if no need to be human readable.
     * 
     * Falls back to random password if a dictionary file is unavailable.
     * 
     * @param boo $readable flag to generate a readable password or a random string
     * @param int $len Length of generated password (only used by non readable password
     * @return array the password and the password to display - e.g. "monkeyhorseapple" and "monkey horse apple"
     */
    public function gen_password($readable, $len = 8) {
      $this->load();
      // Revert to default password generation if no dictionary.
      if ($readable === false or !$this->is_readable()) {
        $lower    = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
        $upper    = 'ABCDEFGHIJKLMN0PQRSTUVWXYZABCDEFGHIJKLMN0PQRSTUVWXYZ';
        $num      = '0123456789012345678901234501234567890123456789012345';
        $special  = '!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-';

        $pass = '';
        $chars = array($lower, $lower, $lower, $special, $num, $num, $upper, $upper);
        for ($i = 0; $i < $len; $i++) {
          if ($i < 7) {
            $pass .= substr($chars[$i], rand(0, 51), 1);
          } else {
            $pass .= substr($chars[rand(2, 6)], rand(0, 51), 1);
          }
        }
          return array('password' => $pass, 'display_password' => $pass);
      }

      $pass = '';
      $disppass = '';
      for ($i = 0; $i < 3; $i++) {
        $word = rtrim($this->dictionary[rand(0, count($this->dictionary))]);
        $pass .= $word;
        $disppass .= $word . ' ';
      }

      return array('password' => $pass, 'display_password' => rtrim($disppass));
    }
    
    /**
     * Check if readable passwords in use.
     * @return bool
     */
    public function is_readable() {
        if (count($this->dictionary) > 0) {
            return true;
        }
        return false;
    }
}