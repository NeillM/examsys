<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

$string['signinmsg'] = 'Strona, którą chcesz zobaczyć wymaga uwierzytelnienia. Zaloguj się używając swojej nazwy użytkownika i hasła:';
$string['username'] = 'Nazwa użytkownika';
$string['password'] = 'Hasło';
$string['signin'] = 'Zaloguj się';
$string['logout'] = 'Wyloguj się';
$string['home'] = 'Strona główna';
$string['help'] = 'Pomoc';
$string['line'] = 'Linia';
$string['cancel'] = 'Anuluj';
$string['ok'] = 'OK'; //cognate
$string['save'] = 'Zachowaj';
$string['accessdenied'] = 'Odmowa dostępu';
$string['accessdeniedadmin'] = '<strong>Brak dostępu</strong><br />Podejmujesz działania dostępne tylko administratorowi.';
$string['accessdeniedsysadmin'] = '<strong>Brak dostępu</strong><br />Podejmujesz działania dostępne tylko administratorowi systemu.';
$string['denied_paper'] = 'Nie posiadasz  <strong>praw dostępu do tego arkusza</strong>!';
$string['denied_folder'] = 'Nie posiadasz  <strong>praw dostępu do tego folderu</strong>!';
$string['denied_question'] = 'Nie posiadasz  <strong>praw dostępu do tego pytania</strong>!';
$string['denied_team'] = 'Nie jesteś członkiem zespołu dla modułu <strong>%s</strong>.';
$string['noldapusernamesupplied'] = 'Nie wprowadzono loginu lub hasła LDAP';
$string['incorrectpassword'] = 'Wprowadzono nieprawidłowe hasło';
$string['ldapserverunavailable'] = 'Serwer LDAP jest niedostępny';
$string['noldapaccount'] = 'Nie odnaleziono konta LDAP !';
$string['ldapservernosearch'] = 'Serwer LDAP niedostępny: wyszukiwanie niemożliwe';
$string['nodatabaseconnection'] = 'Podłączenie do bazy niemożliwe - skontaktuj się z administratorem';
$string['as'] = 'jako';
$string['localtsauthfailed'] = 'Nie uzyskano uwierzytelniania Rogō.';
$string['notsaccount'] = 'Nie odnaleziono konta Rogō!';
$string['tempaccount'] = 'Konto tymczasowe';
$string['authenticationfailed'] = 'Nie uzyskano uwierzytelnienia Rogō, należy pamiętać:';
$string['usernamecasesensitive'] = 'W loginie i haśle rozróżniane są duże i małe litery';
$string['pressf5'] = "Wybierz 'F5' na klawiaturze aby logować się ponownie";
$string['tsonldap'] = 'W Rogō stosowane są hasła LDAP';
$string['ifstuckinvigilator'] = 'W razie dalszych problemów poproś osobę nadzorującą o';
$string['forgottenpassword'] = 'Zapomniane hasło';
$string['warning'] = 'Uwaga:';
$string['secureconnection'] = 'Wymagane bezpieczne połączenie';
$string['secureconnectionmsg'] = 'Rogō jest dostępny wyłącznie przez połączenie bezpieczne. W zamian użyj:';
$string['errormsg'] = 'Jeśli kolejny raz napotkałeś ten błąd skontaktuj się z:';
$string['ltifirstlogin'] = 'Autoryzacja Rogō';
$string['ltifirstlogindesc'] = 'System egzaminowania Rogō wymaga identyfikacji Twojej osoby, aby upewnić się co do posiadanych przez Ciebie przywilejów. Dokonane to będzie tylko przy pierwszej wizycie i w nielicznych przypadkach, w celu zapewnienia bezpieczeństwa w przyszłości.<br /><a href="./">Kliknij, aby kontynuować</a> ';
$string['ltinotconfigured'] = 'Hiperłącze, które wybrałeś nie jest jeszcze skonfigurowane.';
$string['LTIFAILURE'] = 'niepowodzenie LTI';
$string['expiredtimestamp'] = 'Datownik tego połączenia jest już przeterminowany. Powróć do poprzedniej strony i ponownie wybierz ten link (nie odświeżaj przeglądarki).';
$string['UserMissing'] = 'Brak stowarzyszonego użytkownika ';
$string['UserMissingDesc'] = 'Z jakiegoś powodu w systemie Rogo brak jest stowarzyszonego użytkownika. To jest poważny błąd dlatego powinieneś skontaktować się z administratorem systemu';

$string['NoAuthenticationConfigured'] = 'No Authentication configured'; //TODO Niko
$string['NoAuthenticationConfiguredmessage'] = 'No Authentication configuration has been set in the config file. Please contact your local system administrator.'; //TODO Niko
$string['Authentication_callback_failure1'] = 'Failed to register callback for section '; //TODO Niko
$string['Authentication_callback_failure2'] = ' with function'; //TODO Niko
$string['Authentication_issue1'] = 'Authentication Issue'; //TODO Niko
$string['Authentication_issue2'] = 'The authentication plugins couldnt log you in and, they the plugins didnt provide any further form or redirect.Press F5 to refresh if this is still unsuccessful please contact support:  <a href="mailto:%s">%s</a>.<p>Please Include the following debug in your email:</p><div style="margin-left:100px;">%s</div>'; //TODO Niko
$string['Authentication_notloggedin1'] = 'Authentication Issue'; //TODO Niko
$string['Authentication_notloggedin2'] = 'You are not logged in.   Press F5 to refresh if this is still unsuccessful please contact support: <a href="mailto:%s">%s</a><p>Please Include the following debug in your email:</p><div style="margin-left:100px;">%s</div>'; //TODO Niko
$string['NoLookupConfigured'] = 'No lookup configured';
$string['NoLookupConfiguredmessage'] = 'No lookup configuration has been set in the config file. Please contact your local system administrator.'; //TODO Niko
$string['guestbutton'] = 'Guest Login'; //TODO Niko
$string['authenticatio_lti_authmessage'] = 'Please Login to authenticate the LTI Connection.'; //TODO Niko
?>
