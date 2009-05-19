<?php
/**
 * Default interface for user authentication.
 * Applications utilizing this file: Vanilla;
 *
 * Copyright 2003 Mark O'Sullivan
 * This file is part of Lussumo's Software Library.
 * Lussumo's Software Library is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * Lussumo's Software Library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with Vanilla; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * The latest source code is available at www.lussumo.com
 * Contact Mark O'Sullivan at mark [at] lussumo [dot] com
 *
 * @author Mark O'Sullivan
 * @copyright 2003 Mark O'Sullivan
 * @license http://lussumo.com/community/gpl.txt GPL 2
 * @package People
 * @version 1.1.7
 */


/**
 * Default interface for user authentication. This class may be
 * replaced with another using the "AUTHENTICATION_MODULE"
 * and "AUTHENTICATION_CLASS" configuration settings.
 * @package People
 */
class Authenticator {
	/**
	 * @var Context
	 */
	var $Context;

	/**
	 * @var PeoplePasswordHash
	 */
	var $PasswordHash;

	// Returning '0' indicates that the username and password combination weren't found.
	// Returning '-1' indicates that the user does not have permission to sign in.
	// Returning '-2' indicates that a fatal error has occurred while querying the database.
	function Authenticate($Username, $Password, $PersistentSession) {
		// Validate the username and password that have been set
		$UserID = 0;
		$UserManager = $this->Context->ObjectFactory->NewContextObject(
			$this->Context, 'UserManager');
		$User = $UserManager->GetUserCredentials(0, $Username);

		if (!$User === null) {
			$UserID = -2;
		} elseif ($User) {
			if ($User->VerificationKey == '') $User->VerificationKey = DefineVerificationKey();

			if ($this->PasswordHash->CheckPassword($User, $Password)) {
				if (!$User->PERMISSION_SIGN_IN) {
					$UserID = -1;
				} else {
					$UserID = $User->UserID;
					$VerificationKey = $User->VerificationKey;

					// 1. Update the user's information
					$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);

					// 2. Log the user's IP address
					$UserManager->AddUserIP($UserID);

					// 3. Assign the session value
					$this->AssignSessionUserID($UserID);

					// 4. Set the 'remember me' cookies
					if ($PersistentSession) $this->SetCookieCredentials($UserID, $VerificationKey);
				}
			}
		}
		return $UserID;
	}

	function Authenticator(&$Context) {
		$this->Context = &$Context;
		$this->PasswordHash = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'PeoplePasswordHash');
	}

	function DeAuthenticate() {
		$this->Context->Session->Destroy();

		// Destroy the cookies as well
		$Cookies = array(
			$this->Context->Configuration['COOKIE_USER_KEY'],
			$this->Context->Configuration['COOKIE_VERIFICATION_KEY']);
		$UseSsl = ($this->Context->Configuration['HTTP_METHOD'] === "https");
		$HttpOnly = (array_key_exists('HTTP_ONLY_COOKIE', $this->Context->Configuration)
			&& $this->Context->Configuration['HTTP_ONLY_COOKIE']);
		foreach($Cookies as $Cookie) {
			// PHP 5.2.0 required for HTTP only parameter of setcookie()
			if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
				setcookie($Cookie,
					' ',
					time()-3600,
					$this->Context->Configuration['COOKIE_PATH'],
					$this->Context->Configuration['COOKIE_DOMAIN'],
					$UseSsl, // Secure connections only
					$HttpOnly); // HTTP only
			} else {
				setcookie($Cookie,
					' ',
					time()-3600,
					$this->Context->Configuration['COOKIE_PATH'],
					$this->Context->Configuration['COOKIE_DOMAIN'],
					$UseSsl); // Secure connections only
			}
			unset($_COOKIE[$Cookie]);
		}
		return true;
	}

	function GetIdentity() {

		$UserID = $this->Context->Session->GetVariable(
			$this->Context->Configuration['SESSION_USER_IDENTIFIER'], 'int');

		if ($UserID == 0) {
			// UserID wasn't found in the session, so attempt to retrieve it from the cookies
			// Retrieve cookie values
			$EncryptedUserID = ForceIncomingCookieString($this->Context->Configuration['COOKIE_USER_KEY'], '');
			$VerificationKey = ForceIncomingCookieString($this->Context->Configuration['COOKIE_VERIFICATION_KEY'], '');
			$UserManager = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'UserManager');

			$UserID = $this->ValidateVerificationKey($UserManager, $EncryptedUserID, $VerificationKey);

			if ($UserID > 0) {
				// 1. Update the user's information
				$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);

				// 2. Log the user's IP address
				$UserManager->AddUserIP($UserID);

				// If it has now been found, set up the session.
				$this->AssignSessionUserID($UserID);
			}
		}
		return $UserID;
	}

	// All methods below this point are specific to this authenticator and
	// should not be treated as interface methods. The only required interface
	// properties and methods appear above.

	function AssignSessionUserID($UserID) {
		if ($UserID > 0) {
			$this->Context->Session->SetVariable(
				$this->Context->Configuration['SESSION_USER_IDENTIFIER'], $UserID);
		}
	}

	/**
	 * Log user ip
	 *
	 * @deprecated
	 * @param int $UserID
	 */
	function LogIp($UserID) {
		if ($this->Context->Configuration['LOG_ALL_IPS']) {
			$UserManager = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'UserManager');
			$UserManager->AddUserIP($UserID);
		}
	}

	/**
	 * Set cookies used for persistent "Session"
	 *
	 * If $Configuration['ENCRYPT_COOKIE_USER_KEY'] is True (in conf/settings.php),
	 * the UserID will be encrypted. In most cases you should be encrypted
	 *
	 * @param int $CookieUserID
	 * @param string $VerificationKey
	 */
	function SetCookieCredentials($CookieUserID, $VerificationKey) {
		// Note: 2592000 is 60*60*24*30 or 30 days

		if (array_key_exists('ENCRYPT_COOKIE_USER_KEY', $this->Context->Configuration)
			&& $this->Context->Configuration['ENCRYPT_COOKIE_USER_KEY']
		) {
			$CookieUserID = md5($CookieUserID);
		}

		$Cookies = array(
			$this->Context->Configuration['COOKIE_USER_KEY'] => $CookieUserID,
			$this->Context->Configuration['COOKIE_VERIFICATION_KEY'] => $VerificationKey);

		$UseSsl = ($this->Context->Configuration['HTTP_METHOD'] === "https");
		$HttpOnly = (array_key_exists('HTTP_ONLY_COOKIE', $this->Context->Configuration)
			&& $this->Context->Configuration['HTTP_ONLY_COOKIE']);
		foreach($Cookies as $Name => $Value) {
			// PHP 5.2.0 required for HTTP only parameter of setcookie()
			if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
				setcookie($Name,
					$Value,
					time()+2592000,
					$this->Context->Configuration['COOKIE_PATH'],
					$this->Context->Configuration['COOKIE_DOMAIN'],
					$UseSsl, // Secure connections only
					$HttpOnly); // HTTP only
			} else {
				setcookie($Name,
					$Value,
					time()+2592000,
					$this->Context->Configuration['COOKIE_PATH'],
					$this->Context->Configuration['COOKIE_DOMAIN'],
					$UseSsl); // Secure connections only
			}
		}
	}

	/**
	 * Update user last visit
	 *
	 * @deprecated
	 * @param int $UserID
	 * @param string $VerificationKey
	 */
	function UpdateLastVisit($UserID, $VerificationKey = '') {
		$UserManager = $this->Context->ObjectFactory->NewContextObject(
			$this->Context, 'UserManager');
		$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);
	}

	/**
	 * Validate user's Verification Key
	 *
	 * Return user's id
	 *
	 * @param UserManager $UserManager
	 * @param string $EncryptedUserID
	 * @param string $VerificationKey
	 * @return int
	 */
	function ValidateVerificationKey($UserManager, $EncryptedUserID, $VerificationKey) {
		$EncryptedUserID = ForceString($EncryptedUserID, '');
		if ($EncryptedUserID && $VerificationKey) {
			$UserIDs = $UserManager->GetUserIdsByVerificationKey($VerificationKey);
			foreach ($UserIDs as $UserID) {
				// For backward compatibility, the UserID might not be encrypted
				if ($EncryptedUserID == $UserID
					|| $EncryptedUserID == md5($UserID)
				) {
					return $UserID;
				}
			}
		}
		return 0;
	}
}
?>