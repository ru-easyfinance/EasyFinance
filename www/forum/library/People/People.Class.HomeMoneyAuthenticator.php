<?php
/*
* Copyright 2003 Mark O'Sullivan
* This file is part of People: The Lussumo User Management System.
* Lussumo's Software Library is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
* Lussumo's Software Library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with Vanilla; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
* The latest source code is available at www.lussumo.com
* Contact Mark O'Sullivan at mark [at] lussumo [dot] com
*
* Description: Default interface for user authentication. This class may be
* replaced with another using the "AUTHENTICATION_MODULE" configuration setting.
* Applications utilizing this file: Vanilla;
*/
class Authenticator {
    var $Context;
    var $SiteSettingsRetrieved;
    var $CookiePath;
    var $SiteCookiePath;
    var $CookieHash;
    var $UserCookieName;
    var $PassCookieName;

    function AssignSessionData($user_login, $user_pass, $PersistentSession = '0') {
        die("AssignSessionData");
        $this->GetSiteSettings();

        $Expire = $PersistentSession ? time() + 31536000 : 0;
        $user_pass = md5($user_pass); // Double hash the password in the cookie.

        // Set the cookies
        setcookie($this->UserCookieName, $user_login, $Expire, $this->CookiePath, false);
        setcookie($this->PassCookieName, $user_pass, $Expire, $this->CookiePath, false);

        if ($this->CookiePath != $this->SiteCookiePath ) {
            setcookie($this->UserCookieName, $user_login, $Expire, $this->SiteCookiePath, false);
            setcookie($this->PassCookieName, $user_pass, $Expire, $this->SiteCookiePath, false);
        }
    }

    // This method is used in various places in Vanilla to sign someone into a session
    function AssignSessionUserID($UserID) {
        die("AssignSessionUserID");
        // Get the login_name and password for session assignment
        $s = $this->Context->ObjectFactory->NewContextObject($this->Context, 'SqlBuilder');
        $s->SetMainTable('User', 'u');
        $s->AddSelect(array('Name', 'Password'), 'u');
        $s->AddWhere('u', 'UserID', '', $UserID, '=');
        $Result = $this->Context->Database->Select($s,
         'HomeMoneyAuthenticator',
         'AssignSessionUserID',
         'An error occurred while attempting to assign session data');

        if ($Result) {
            $user_login = '';
            $user_pass = '';
            while ($rows = $this->Context->Database->GetRow($Result)) {
                $user_login = ForceString($rows['Name'], '');
                $user_pass = ForceString($rows['Password'], '');
            }
            $this->AssignSessionData($user_login, $user_pass);
        }
    }

    // Returning '0' indicates that the username and password combination weren't found.
    // Returning '-1' indicates that the user does not have permission to sign in.
    // Returning '-2' indicates that a fatal error has occurred while querying the database.
    function Authenticate($Username, $Password, $PersistentSession) {
       die("Authenticate");
      // Validate the username and password that have been set
      $Username = FormatStringForDatabaseInput($Username);
      $Password = FormatStringForDatabaseInput($Password);
      $UserID = 0;
        $user_login = '';
        $user_pass = '';

      // Retrieve matching username/password values
      $s = $this->Context->ObjectFactory->NewContextObject($this->Context, 'SqlBuilder');
      $s->SetMainTable('User', 'u');
      $s->AddJoin('Role', 'r', 'RoleID', 'u', 'RoleID', 'inner join');
      $s->AddSelect(array('UserID', 'Name', 'Password'), 'u');
      $s->AddSelect('PERMISSION_SIGN_IN', 'r');
      $s->AddWhere('u', 'Name', '', $Username, '=');
      $s->AddWhere('u', 'Password', '', md5($Password), '=', 'and', '', 1, 1);
      $s->AddWhere('u', 'Password', '', $Password, '=', 'or');
      $s->EndWhereGroup();

      $UserResult = $this->Context->Database->Select($s,
         'HomeMoneyAuthenticator',
         'Authenticate',
         'An error occurred while attempting to validate your credentials');

      if (!$UserResult) {
         $UserID = -2;
      } elseif ($this->Context->Database->RowCount($UserResult) > 0) {
         $CanSignIn = 0;
         $VerificationKey = '';
         while ($rows = $this->Context->Database->GetRow($UserResult)) {
                $UserID = ForceInt($rows['UserID'], 0);
            $user_login = ForceString($rows['Name'], '');
            $user_pass = ForceString($rows['Password'], '');
            $CanSignIn = ForceBool($rows['PERMISSION_SIGN_IN'], 0);
         }
         if (!$CanSignIn) {
            $UserID = -1;
         } else {
            // Update the user's information
            $this->UpdateLastVisit($UserID);

            // Assign the session value
            $this->AssignSessionData($user_login, $user_pass, $PersistentSession);

                // Log the person's ip
            $this->LogIp($UserID);
         }
      }
      return $UserID;
   }

    function Authenticator(&$Context) {
        $this->Context = &$Context;
        $this->SiteSettingsRetrieved = '0';
        $this->CookiePath = '';
        $this->SiteCookiePath = '';
        $this->CookieHash = '';
    }

    function DeAuthenticate() {
        die('DeAuthenticate');
        if (session_id()) session_destroy();
        $this->GetSiteSettings();
        setcookie($this->UserCookieName, ' ', time() - 31536000, $this->CookiePath, false);
        setcookie($this->PassCookieName, ' ', time() - 31536000, $this->CookiePath, false);
        setcookie($this->UserCookieName, ' ', time() - 31536000, $this->SiteCookiePath, false);
        setcookie($this->PassCookieName, ' ', time() - 31536000, $this->SiteCookiePath, false);
        return true;
    }

    function GetIdentity() {
        $this->GetSiteSettings();

        // Examine the cookie values for session info
        $login = ForceIncomingCookieString($this->UserCookieName, '');
        $pass = ForceIncomingCookieString($this->PassCookieName, '');
        $dbpass = '';
        $UserID = 0;

        if ($login != '' && $pass != '') {
            $s = "SELECT user_id, user_pass FROM users WHERE user_login = '".FormatStringForDatabaseInput($login)."'";

            $Result = $this->Context->Database->Execute($s,
                'HomeMoneyAuthenticator',
                'GetIdentity',
                'An error occurred while attempting to retrieve session data.');

            if ($Result) {
                while ($rows = $this->Context->Database->GetRow($Result)) {
                    //die(var_dump($rows));
                    $UserID = ForceString($rows['user_id'], '');
                    $dbpass = ForceString($rows['user_pass'], '');
                }

                // If the double-md5d password doesn't match the one in the cookie - don't authenticate
                if (md5($dbpass) != $pass) {
                    $UserID = 0;
                }
            }
        } else {
            if (session_id()) session_destroy();
        }

        if (!session_id()) {
            session_set_cookie_params(0, $this->CookiePath);
            session_start();
        }
        return $UserID;
    }

    function GetSiteSettings() {
        if ($this->SiteSettingsRetrieved == '0') {
            $siteurl = 'http://hm';
            $home = '/forum/';
//            $s = "select option_name, option_value from wp_options where option_name = 'siteurl' or option_name = 'home'";
//            $DataSet = $this->Context->Database->Execute($s,
//                'HomeMoneyAuthenticator',
//                'GetCookieName',
//                'An error occurred while retrieving cookie names.');
//            if ($DataSet) {
//                while ($rows = $this->Context->Database->GetRow($DataSet)) {
//                    if ($rows['option_name'] == 'siteurl') {
//                        $siteurl = ForceString($rows['option_value'], 'http://hm/');
//                    } else if ($rows['option_name'] == 'home') {
//                        $home = ForceString($rows['option_value'], '');
//                    }
//                }
//            }
            $this->CookiePath     = preg_replace('|https?://[^/]+|i', '', $home . '/' );
            $this->SiteCookiePath = preg_replace('|https?://[^/]+|i', '', $siteurl . '/' );
            $this->CookieHash     = md5($siteurl);
            $this->UserCookieName = 'autoLogin';
            $this->PassCookieName = 'autoPass';
            $this->SiteSettingsRetrieved = '1';
        }
    }

    function LogIp($UserID) {
        die('LogIp');
        if ($this->Context->Configuration['LOG_ALL_IPS']) {
            $s = $this->Context->ObjectFactory->NewContextObject($this->Context, 'SqlBuilder');
            $s->SetMainTable('IpHistory', 'i');
            $s->AddFieldNameValue('UserID', $UserID);
            $s->AddFieldNameValue('RemoteIp', GetRemoteIp(1));
            $s->AddFieldNameValue('DateLogged', MysqlDateTime());

            $this->Context->Database->Insert($s,
            'HomeMoneyAuthenticator',
            'LogIp',
            'An error occurred while logging your IP address.',
            false); // fail silently
        }
    }

    function UpdateLastVisit($UserID) {
        die('UpdateLastVisit');
        $s = $this->Context->ObjectFactory->NewContextObject($this->Context, 'SqlBuilder');
        $s->SetMainTable('User', 'u');
        $s->AddFieldNameValue('DateLastActive', MysqlDateTime());
        $s->AddFieldNameValue('CountVisit', 'CountVisit + 1', 0);
        $s->AddWhere('u', 'UserID', '', $UserID, '=');

        $this->Context->Database->Update($s,
         'HomeMoneyAuthenticator',
         'UpdateLastVisit',
         'An error occurred while updating your profile.',
         false); // fail silently
    }
}
?>