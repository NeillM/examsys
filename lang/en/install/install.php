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

$string['company'] = 'Company';
$string['companyname'] = 'Company Name';
$string['databaseadminuser'] = 'Database Admin User';
$string['server'] = 'Server';
$string['tempdirectory'] = 'Temp Directory';
$string['needusername'] = 'The installer need the username and password of a MySQL admin user to create the database and required tables. This username is not saved to the server and is only used by this install script.';
$string['dbusername'] = 'DB Username';
$string['dbpassword'] = 'DB Password';
$string['databasesetup'] = 'Database Setup';
$string['databasehost'] = 'Database host';
$string['webhost'] = 'WebServer host';
$string['datadirectory'] = 'Rogō data directory';
$string['databaseport'] = 'Database port';
$string['databasename'] = 'Database Name';
$string['databaseuser'] = 'Rogō Database user';
$string['rdbusername'] = 'Username';
$string['rdbpassword'] = 'Password';
$string['timedateformats'] = 'Time/Date formats';
$string['date'] = 'Date (MySQL)';
$string['longdate'] = 'Long Date (MySQL)';
$string['shortdatetime'] = 'Short Date/Time (MySQL)';
$string['longdatetime'] = 'Long Date/Time (MySQL)';
$string['longdatephp'] = 'Long date (PHP)';
$string['shortdatephp'] = 'Short date (PHP)';
$string['longtimephp'] = 'Long time (PHP)';
$string['shorttimephp'] = 'Short time (PHP)';
$string['searchleadinlength'] = 'Search results lead-in length';
$string['currenttimezone'] = 'Current Timezone';
$string['authentication'] = 'Authentication';

$string['allowlti'] = 'Allow via LTI';
$string['allowltitip'] = 'Allow authentication from successful LTI launch';
$string['allowintdb'] = 'Internal database';
$string['allowintdbtip'] = 'Allow authentication from internal Rogo user database';
$string['allowguest'] = 'Guest log in (for summative exams)';
$string['allowguesttip'] = 'Allow guest temporary accouts for students who forget their normal log in details';
$string['allowimpersonation'] = 'User impersonation (SysAdmin only)';
$string['allowimpersonationtip'] = 'Allow SysAdmin users to impersonate other users';
$string['useldap'] = 'Use LDAP';

$string['lookup'] = 'Lookup Data Sources';
$string['allowlookupXML'] = 'use XML (Will need customising in config file)';
$string['allowlookupXMLtip'] = 'Allow a custom XML lookup source.';
$string['rdbbasename'] = 'Basepart of username';

$string['ldapserver'] = 'LDAP server';
$string['searchdn'] = 'Search dn';
$string['bindusername'] = 'bind username';
$string['bindpassword'] = 'bind password';
$string['userprefix'] = 'Username prefix';
$string['userprefixtip'] = 'Prefix for username in LDAP search, e.g. sAMAccountName=';
$string['sysadminuser'] = 'Rogō SysAdmin User';
$string['initialsysadmin'] = 'An initial SysAdmin user account is required to log in and create further normal staff accounts and generally administer the system.';
$string['title'] = 'Title';
$string['title_types'] = "Mx,Mr,Mrs,Miss,Ms,Dr,Professor";
$string['firstname'] = 'First Name';
$string['surname'] = 'Surname';
$string['emailaddress'] = 'Email Address';
$string['username'] = 'Username';
$string['password'] = 'Password';
$string['helpdb'] = 'Rogō Help Database';
$string['loadhelp'] = 'Load Help';
$string['translationpack'] = 'Rogō Translations';
$string['loadtranslations'] = 'Download All Translation Packs';
$string['manualtranslations'] = 'Alternatively you can download individual translation packs';
$string['supportemail'] = 'Support Email';
$string['supportnumbers'] = 'Emergency Support Numbers';
$string['supportemailaddress'] = 'Email Address (Comma seperate list)';
$string['name'] = 'Name';
$string['number'] = 'Number';
$string['install'] = 'Install Rogō';
$string['installed'] = 'Rogō is now successfully installed.';
$string['config'] = 'Configure settings';
$string['invalidsetting'] = 'Setting %s either not provided or invalid!';

$string['logwarning1'] = 'could not load staff_help.sql, could not install staff help';
$string['logwarning2'] = 'cannot find staff_help.sql, could not install staff help';
$string['logwarning3'] = 'could not load student_help.sql, could not install student help';
$string['logwarning4'] = 'cannot find student_help.sql, could not install student help';
$string['displayerror1'] = 'The database name \'%s\' is in use please use a different one';
$string['displayerror2'] = 'The database \'%s\' could not be created please check the admin users permissions';
$string['displayerror3'] = 'could not create table.';
$string['wdatabaseuser'] = 'Database user ';
$string['wnotcreated'] = ' could not be created';
$string['wnotpermission'] = ' could not set permissions';
$string['logwarning20'] = 'Unable to FLUSH PRIVILEGES';
$string['errors1'] = 'Rogō has already been installed! Please contact a system adminstrator.';
$string['errors3'] = 'Rogō requires %s to exist and be writeable to the webserver';
$string['errors7'] = 'Rogō requires %s/temp to exist and be writeable to the webserver';
$string['errors10'] = 'Rogō requires PHP version %s or above you have %s';
$string['errors11'] = 'Rogō requires the PHP %s module to function please install or activate it.';
$string['errors12'] = 'Rogō can only be accessed through https. Please update you apache config.';
$string['errors13'] = 'Error';
$string['errors14'] = 'The following warnings were generated';
$string['errors15'] = 'Warning';
$string['errors16'] = 'Rogō requires ability to write its config file %s/config/config.inc.php. One way to fix this is you can temporarily allow write access to %s/config and change permissions once update has run.';
$string['errors18'] = 'The Rogō data directory requires %s path to exist';
$string['errors19'] = 'The Rogō data directory requires %s must be writable';
$string['installscript'] = 'Rogō Install script';
$string['systeminstallation'] = 'System Installation';
$string['labsecuritytype'] = "Summative Exam Lab Security";
$string['labsecuritytypetip'] = 'Rogo can lock summative exams to either IP address or hostname. If your institution uses static IPs then chose IP address otherwise chose hostname.';
$string['IP'] = "IP address";
$string['hostname'] = "Machine hostname";

$string['databaseengine'] = "Main Database Engine";
$string['databasehelpengine'] = "Help Database Engine";
$string['databaseenginetooltip'] = "InnoDB reccommended";
$string['helpdatabaseenginetooltip'] = "MyISAM recommended if MySQL version < 5.5";

$string['cannotextract'] = 'Cannot extract language packs, you will need to manually extract them.';
$string['cannotdownloadxml'] = 'Error downloading language packs, you will need to manually install them.';
$string['cannotdownloadzip'] = 'Error downloading latest languages.xml, you may need to manually install it.';
$string['langsuccess'] = 'Language packs installed.';