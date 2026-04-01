<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CoSign config implementation.
 *
 * This is an alternative to adding the configuration to the $authentication array in /config/config.php,
 * you will likely have slightly better performance doing that. This file can act as a reference for
 * the configuration you will need to do that.
 *
 * Added by default below 'impersonation', change the `$cosign_auth_after` variable below to suit.
 *
 * Copy this file to /config/cosign.inc.php and then add: `require_once $root . 'config/cosign.inc.php';`
 * to your /config/config.php file.
 *
 * @author Richard Aspden (GetJohn) <richard@getjohn.co.uk>
 * @copyright Copyright (c) 2018 GetJohn
 */

$cosign_auth_after = 'impersonation';

foreach ($authentication as $index => $auth_method) {
  if ($auth_method[0] != $cosign_auth_after) {
      continue;
  }
  $cosign_auth_after_index = $index;
  break;
}

array_splice($authentication, $cosign_auth_after_index, 0, [[
    'cosign',
    [
        'table' => 'users',
        'username_col' => 'username',
        'id_col' => 'id',
        'cosign_button' => true,
        'disable_ldapmissing' => true,
        'usernamefield' => 'REMOTE_USER',
        'students_only' => true,
        'cosign_cfg' => [
            'CosignProtected' => true, // Enable Cosign Authentication
            'CosignHostname' => 'cosign.server.hostname', // Hostname of server running cosignd
            'CosignPort' => 6663, // The port on which cosignd listens
            'CosignService' => 'service-cookie-name', // The name of cosign service cookie, without the cosign- at the beginning
            'CosignRedirect' => 'https://weblogin.server.hostname/', // The URL to redirect for login
            'CosignFilterDB' => '/var/cosign/filter/', // Filter DB directory. Must end with trailing slash
            'CosignCookieExpireTime' => 3600*24, // Expiration time of service cookie in seconds, default 24 hours
            'CosignFilterLog' => '/tmp/cosign-filter.log', // Debug log file path
            'CosignFilterDebug' => true, // (true/1 for standard debugging, 2 for backtrace)
            'CosignProtocolVersion' => 3, // Version of CoSign protocol
            'CosignPostErrorRedirect' => 'https://weblogin.server.hostname/cosign/post_error.html', // User redirection URL if an error is encountered during a POST
            'CosignRequireFactor' => '', // A list space separated factors that must be satisfied by the user
            'CosignFactorSuffix' => '-junk', // Suffix, that is ignored in CoSign factors
            'CosignFactorSuffixIgnore' => false, // Toggles whether the value of CosignFactorSuffix is ignored
            'CosignSiteEntry' => '', // URL to which the user is redirected after login
            'CosignHTTPOnly' => false, // Use only http protocol to redirect back after login
            'CosignCheckIP' => 'initial', // Verify browser's IP against cosignd's IP information (no/initial/always)
            'CosignFilterHashLength' => 0, // Subdirectory hash length (0,1,2) for Cosign filter cookie file storage
            'CosignCryptoLocalCert' => '/path/to/cert&keyfile.pem', // PEM encoded certificate and private key
            'CosignCryptoVerifyPeer' => 1, // Require verification of server certificate
            'CosignCryptoAllowSelfSigned' => false, // Allow self-signed certificates
            'CosignCryptoCAFile' => '/path/to/CAcertificate.pem', // CA certificate which should be used to verify server certificate
            'CosignCryptoCAPath' => '/path/to/CAdir', // CA certificate which should be used to verify server certificate
            'CosignGetKerberosTickets' => '', //  Toggles whether the value of TGT will be requested from cosignd
            'CosignTicketPrefix' => '/var/cosign/tickets/', // Kerberos ticket filter DB directory. Must end with trailing slash
            // Used in valid.php
            'CosignValidLocation' => '/cosign/valid.php', // Known location of script used for the validation on return from the CoSign server
            'CosignValidationErrorRedirect' => 'https://weblogin.server.hostname/cosign/validation_error.html', // Where to redirect on validation error
            'CosignValidReference' => '~https?://(.*).institution.edu~', // preg_match compatible URL string
            // Currently unused
            'CosignGetProxyCookies' => false, // Toggles whether proxy cookies will be requested from cosignd
            'CosignProxyDB' => '/var/cosign/proxy/', // Cosign filter proxy DB directory. Must end with trailing slash (not implemented)
            'CosignCryptoPassphrase' => '', // Passphrase for private key (if private key is protected)
            // Moved from settings to cosign_cfg:
            'CosignIdleTime' => 60, // Was IDLE_TIME
            'CosignSocketTimeout' => 10, // Was SOCKET_TIMEOUT
        ],
        'CoSign'
    ]
]]);
