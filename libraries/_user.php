<?php

include_once( "utilities.php" );

class User
{

	private static $currentUser = null;
	public $userAccountId = 0;
	public $emailAddress = "";
	public $password = "";
	public $passwordHash = "";
	public $accessHash = "";
	public $powerLevel = UserPowerLevels\NORMAL;
	public $firstName = "";
	public $middleName = "";
	public $lastName = "";
	public $phoneNumber = "";
	public $joinDate = "";
	public $userAccountStatusID = 0;
	public $userAccountStatus = "";
	public $lastIP = "";
	public $lastLoginDate = "";
	// Whether or not the user is logged in.
	public $loggedIn = false;
	public $emailVerificationToken = "";
	public $passwordChangeToken = "";
	public $emailVerified = false;
	public $currentIP = "";

	// ------------------------------------------------------------------------
	// Function: __construct
	// Abstract: If a username and password were specified, login. Otherwise,
	// 			 get the ID only, and only if the login cookie is set. Also call
	// 			 session_start( ) if it hasn't been yet
	// ------------------------------------------------------------------------
	public function __construct( $email = "", $password = "" )
	{
		$this->currentIP = $_SERVER[ "REMOTE_ADDR" ];


		// If the email and password were provided
		if( $email != "" && $password != "" )
		{
			$this->login( $email, $password );
		}
	}

	// ------------------------------------------------------------------------
	// Function: currentUser
	// Abstract: Get the current user. If there is none, create a new instance
	// ------------------------------------------------------------------------
	public static function currentUser()
	{
		if( self::$currentUser == null )
		{
			self::$currentUser = new self( );
			self::$currentUser->checkAuthorization();
		}

		/**
		 * @return User The current User.
		 */
		return self::$currentUser;
	}

	// ------------------------------------------------------------------------
	// Function: setUserPowerLevel
	// Abstract: Set the power level of the specified user
	// ------------------------------------------------------------------------
	public static function setUserPowerLevel( $userID, $powerLevel )
	{
		$result = false;

		$TUA = new SQL\Tables\user_accounts;

		$result = SQL::query( "
			UPDATE
				$TUA
			SET
				$TUA->power_level = ?
			WHERE
				$TUA->user_account_id = ?
			", $powerLevel, $userID );

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: signUp
	// Abstract: Sign up with the specified information
	// ------------------------------------------------------------------------
	public function signUp( $emailAddress, $rawPassword, $firstName, $lastName )
	{
		$result = true;

		$TUA = new SQL\Tables\user_accounts( );
		$TUAS = new SQL\Tables\user_account_statuses( );

		if( $result == true )
		{
			// Hash the password
			$passwordHash = self::passwordHash( $rawPassword );


			// Default to normal user
			$powerLevel = UserPowerLevels\NORMAL;

			$statusId = 1;

			if( !SQL::query( "SELECT $TUAS->user_account_status_id AS STATUS_ID FROM $TUAS WHERE $TUAS->user_account_status = 'active'" ) )
			{
				error_log( "Error: Failed to get 'active' status ID from database." );
			}
			else
			{
				$statusId = SQL::fetch()[ "STATUS_ID" ];
			}

			if( !SQL::query( "SELECT $TUA->email_address AS EMAIL FROM $TUA" ) )
			{
				error_log( "Error: Failed to get email address from database." );
			}
			else
			{
				// If this is the first user
				if( SQL::rowCount() == 0 )
				{
					$powerLevel = UserPowerLevels\DEVELOPER;
				}
			}

			$query = "INSERT INTO
						$TUA
						(
							$TUA->email_address,
							$TUA->password_hash,
							$TUA->access_hash,
							$TUA->power_level,
							$TUA->first_name,
							$TUA->middle_name,
							$TUA->last_name,
							$TUA->phone_number,
							$TUA->join_date,
							$TUA->status_id,
							$TUA->email_verified
						)
						VALUES
						(
							?, ?, '', $powerLevel, ?, '', ?, '', '" . date( "Y-m-d H:i:s" ) . "', $statusId, FALSE
						)";

			$result = SQL::query( $query, $emailAddress, $passwordHash, $firstName, $lastName );

			// If the signup was successful, send a verification email
			if( $result == true )
			{
				$result = $this->login( $emailAddress, $rawPassword );

				$this->requestEmailVerification();
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: requestEmailVerification
	// Abstract: Insert the email verification token and send the email
	// ------------------------------------------------------------------------
	public function requestEmailVerification()
	{
		$result = false;

		$TUA = new SQL\Tables\user_accounts( );
		$TEVT = new SQL\Tables\email_verification_tokens( );

		$query = "
			UPDATE
				$TUA
			SET
				$TUA->email_verified = FALSE
			WHERE
				$TUA->user_account_id = ?
			";

		$result = SQL::query( $query, $this->userAccountId );

		if( $result == true )
		{
			$query = "
				DELETE FROM
					$TEVT
				WHERE
					$TEVT->user_account_id = ?
				";

			$result = SQL::query( $query, $this->userAccountId );

			if( $result == true )
			{
				$this->emailVerificationToken = self::emailVerificationToken( $this->emailAddress );
				self::insertEmailVerificationToken( $this->emailVerificationToken, 3650 );

				// For the email
				$user = $this;

				ob_start();
				include( ROOT . "emails/email-verification.php" );
				$body = ob_get_clean();

				sendEmail( $this->emailAddress, "Verify Email Address", $body );
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: insertEmailVerificationToken
	// Abstract: Insert the email verification token
	// ------------------------------------------------------------------------
	public function insertEmailVerificationToken( $token, $lifespanInDays )
	{
		$TEVT = new SQL\Tables\email_verification_tokens( );

		$query = "
			INSERT INTO
				$TEVT
				(
					$TEVT->email_verification_token,
					$TEVT->user_account_id,
					$TEVT->expiration_date
				)
				VALUES
				(
					'$token', " . $this->userAccountId . ", '" . date( "Y-m-d H:i:s", strtotime( "+" . $lifespanInDays . " day" ) ) . "'
				)
			";

		$result = SQL::query( $query );
		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: verifyEmailAddress
	// Abstract: Verify the email address with the specified token
	// ------------------------------------------------------------------------
	public static function verifyEmailAddress( $token )
	{
		$result = false;

		$userAccountId = 0;

		$TUA = new SQL\Tables\user_accounts( );
		$TEVT = new SQL\Tables\email_verification_tokens( );

		$query = "
			SELECT
				$TEVT->email_verification_token_id	AS EMAIL_VERIFICATION_TOKEN_ID,
				$TEVT->email_verification_token		AS EMAIL_VERIFICATION_TOKEN,
				$TEVT->user_account_id				AS USER_ACCOUNT_ID,
				$TEVT->expiration_date				AS EXPIRATION_DATE
			FROM
				$TEVT
			WHERE
				$TEVT->email_verification_token = ?
			";

		$result = SQL::query( $query, $token );


		if( SQL::rowCount() == 0 )
		{
			$result = false;
		}


		if( $result == true )
		{
			if( $row = SQL::fetch() )
			{
				$currentDate = new DateTime( );

				$expirationDate = null;

				if( $row[ "EXPIRATION_DATE" ] !== null )
				{
					$expirationDate = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "EXPIRATION_DATE" ] );
				}

				// If the token is valid and not expired
				if( $token == $row[ "EMAIL_VERIFICATION_TOKEN" ] && ( $expirationDate === null || $currentDate <= $expirationDate ) )
				{
					$userAccountId = $row[ "USER_ACCOUNT_ID" ];

					$query = "
						UPDATE
							$TUA
						SET
							$TUA->email_verified = TRUE
						WHERE
							$TUA->user_account_id = ?
						";

					$result = SQL::query( $query, $userAccountId );
				}
				else
				{
					$result = false;
				}
			}
		}

		if( $result == true && $userAccountId != 0 )
		{
			$query = "
				DELETE FROM
					$TEVT
				WHERE
					$TEVT->user_account_id = ?
				";

			SQL::query( $query, $userAccountId );
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: requestPasswordChange
	// Abstract: Insert the password change token and send the email
	// ------------------------------------------------------------------------
	public static function requestPasswordChange( $emailAddress )
	{
		$result = false;

		$TPCT = new SQL\Tables\password_change_tokens( );

		// For the email
		$user = self::getUserByEmail( $emailAddress );

		if( $user != null )
		{
			$query = "
				DELETE FROM
					$TPCT
				WHERE
					$TPCT->user_account_id = ?
				";

			$result = SQL::query( $query, $user->userAccountId );

			if( $result == true )
			{
				$token = self::passwordChangeToken( $emailAddress );
				$result = self::insertPasswordChangeToken( $token, $user, 1 );
				$user->passwordChangeToken = $token;

				if( $result == true )
				{
					ob_start();
					include( ROOT . "emails/password-change.php" );
					$body = ob_get_clean();

					$result = sendEmail( $emailAddress, "Password Change", $body );
				}
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: insertPasswordChangeToken
	// Abstract: Insert the password change token
	// ------------------------------------------------------------------------
	public static function insertPasswordChangeToken( $token, $user, $lifespanInDays )
	{
		$TPCT = new SQL\Tables\password_change_tokens( );

		$query = "
			INSERT INTO
				$TPCT
				(
					$TPCT->password_change_token,
					$TPCT->user_account_id,
					$TPCT->expiration_date
				)
				VALUES
				(
					'$token', " . $user->userAccountId . ", '" . date( "Y-m-d H:i:s", strtotime( "+" . $lifespanInDays . " day" ) ) . "'
				)
			";

		$result = SQL::query( $query );
		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: changePassword
	// Abstract: Change the user's password to the specified new password
	// ------------------------------------------------------------------------
	public static function changePassword( $token, $newPassword )
	{
		$result = false;

		$userAccountId = 0;

		$TUA = new SQL\Tables\user_accounts( );
		$TPCT = new SQL\Tables\password_change_tokens( );

		$query = "
			SELECT
				$TPCT->password_change_token_id		AS PASSWORD_CHANGE_TOKEN_ID,
				$TPCT->password_change_token		AS PASSWORD_CHANGE_TOKEN,
				$TPCT->user_account_id				AS USER_ACCOUNT_ID,
				$TPCT->expiration_date				AS EXPIRATION_DATE
			FROM
				$TPCT
			WHERE
				$TPCT->password_change_token = ?
			";

		$result = SQL::query( $query, $token );


		if( SQL::rowCount() == 0 )
		{
			$result = false;
		}


		if( $result == true )
		{
			if( $row = SQL::fetch() )
			{
				$currentDate = new DateTime( );

				$expirationDate = null;

				if( $row[ "EXPIRATION_DATE" ] !== null )
				{
					$expirationDate = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "EXPIRATION_DATE" ] );
				}

				// If the token is valid and not expired
				if( $token == $row[ "PASSWORD_CHANGE_TOKEN" ] && ( $expirationDate === null || $currentDate <= $expirationDate ) )
				{
					$userAccountId = $row[ "USER_ACCOUNT_ID" ];

					// Hash the password
					$passwordHash = self::passwordHash( $newPassword );

					$query = "
						UPDATE
							$TUA
						SET
							$TUA->password_hash = ?
						WHERE
							$TUA->user_account_id = ?
						";

					$result = SQL::query( $query, $passwordHash, $userAccountId );
				}
				else
				{
					$result = false;
				}
			}
		}

		if( $result == true && $userAccountId != 0 )
		{
			$query = "
				DELETE FROM
					$TPCT
				WHERE
					$TPCT->user_account_id = ?
				";

			SQL::query( $query, $userAccountId );
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: getUserByEmail
	// Abstract: Returns an instance of User with data for the user with the
	//			 specified email address.
	// ------------------------------------------------------------------------
	public static function getUserByEmail( $emailAddress )
	{
		$user = null;

		// Aliases for SQL query
		$TUA = new SQL\Tables\user_accounts( );
		$TUAS = new SQL\Tables\user_account_statuses( );

		// Select all data about the specified user
		$query = "
		SELECT
			$TUA->user_account_id			AS USER_ACCOUNT_ID,
			$TUA->email_address				AS EMAIL_ADDRESS,
			$TUA->password_hash				AS PASSWORD_HASH,
			$TUA->access_hash				AS ACCESS_HASH,
			$TUA->power_level				AS POWER_LEVEL,
			$TUA->first_name				AS FIRST_NAME,
			$TUA->middle_name				AS MIDDLE_NAME,
			$TUA->last_name					AS LAST_NAME,
			$TUA->phone_number				AS PHONE_NUMBER,

			$TUA->join_date					AS JOIN_DATE,

			$TUA->status_id					AS STATUS_ID,
			$TUAS->user_account_status		AS USER_ACCOUNT_STATUS,
			$TUA->email_verified			AS EMAIL_VERIFIED

		FROM
			$TUA INNER JOIN $TUAS
			ON( $TUA->status_id = $TUAS->user_account_status_id )
		WHERE
			( $TUA->email_address 	= ?
		AND   $TUA->email_address 	!= '' )
		LIMIT 1
		";

		if( SQL::query( $query, $emailAddress ) )
		{
			if( $row = SQL::fetch() )
			{
				$user = new self( );
				$user->loadFromRow( $row );
			}
		}

		return $user;
	}

	// ------------------------------------------------------------------------
	// Function: login
	// Abstract: Login with the specified email and password
	// ------------------------------------------------------------------------
	public function login( $emailAddress, $rawPassword )
	{
		$result = true;

		$TULH = new SQL\Tables\user_login_history( );

		$result = $this->loginQueries( $emailAddress, $rawPassword );

		error_log( "login result: " . $result);

		// If the user logged in with email/password
		if( $result == true )
		{
			// Generate a new authorization hash
			$this->accessHash = $this->accessHash( $emailAddress );

			$result = $this->authorize();
			error_log( "authorize result: " . $result);

			if( $result == true )
			{
				$query = "
					INSERT INTO $TULH( $TULH->user_account_id, $TULH->login_date, $TULH->ip_address )
					VALUES( $this->userAccountId, '" . date( "Y-m-d H:i:s" ) . "', '" . IP_ADDRESS . "' )
					";

				$result = SQL::query( $query );
			}
		}

		$this->loggedIn = $result;


		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: authorize
	// Abstract: Authorize this user after a login
	// ------------------------------------------------------------------------
	private function authorize()
	{
		$result = false;

		$TUA = new SQL\Tables\user_accounts( );

		$query = "
		UPDATE
			$TUA
		SET
			$TUA->access_hash = ?
		WHERE
			$TUA->user_account_id = ?
		";

		if( SQL::query( $query, $this->accessHash, $this->userAccountId ) )
		{
			COOKIE::set( "auth", $this->accessHash, time() + 86400 );
			$result = true;
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: deauthorize
	// Abstract: Deauthorize this user after a login
	// ------------------------------------------------------------------------
	private function deauthorize()
	{
		COOKIE::delete( "auth" );
	}

	// ------------------------------------------------------------------------
	// Function: checkAuthorization
	// Abstract: Check if the user has valid authentication cookies
	// ------------------------------------------------------------------------
	protected function checkAuthorization()
	{
		$result = false;

		// If the cookies are set
		if( COOKIE::get( "auth" ) !== null )
		{
			$result = $this->loginQueries( "", "", COOKIE::get( "auth" ) );
		}

		$this->loggedIn = $result;

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: loginQueries
	// Abstract: Run queries to login with EITHER an email address OR an access hash
	// ------------------------------------------------------------------------
	private function loginQueries( $email, $rawPassword, $accessHash = "" )
	{
		$result = false;

		// Aliases for SQL query
		$TUA = new SQL\Tables\user_accounts( );
		$TUAS = new SQL\Tables\user_account_statuses( );

		// Select all data about the specified user
		$query = "
		SELECT
			$TUA->user_account_id			AS USER_ACCOUNT_ID,
			$TUA->email_address				AS EMAIL_ADDRESS,
			$TUA->password_hash				AS PASSWORD_HASH,
			$TUA->access_hash				AS ACCESS_HASH,
			$TUA->power_level				AS POWER_LEVEL,
			$TUA->first_name				AS FIRST_NAME,
			$TUA->middle_name				AS MIDDLE_NAME,
			$TUA->last_name					AS LAST_NAME,
			$TUA->phone_number				AS PHONE_NUMBER,

			$TUA->join_date					AS JOIN_DATE,

			$TUA->status_id					AS STATUS_ID,
			$TUAS->user_account_status		AS USER_ACCOUNT_STATUS,
			$TUA->email_verified			AS EMAIL_VERIFIED

		FROM
			$TUA INNER JOIN $TUAS
			ON( $TUA->status_id = $TUAS->user_account_status_id )
		WHERE
			( $TUA->email_address 	= ?
		AND   $TUA->email_address 	!= '' )
		OR	( $TUA->access_hash 	= ?
		AND   $TUA->access_hash 	!= '' )
		LIMIT 1
		";

		if( SQL::query( $query, $email, $accessHash ) )
		{
			if( $row = SQL::fetch() )
			{
				// If the credentials are valid
				if( password_verify( $rawPassword, $row[ "PASSWORD_HASH" ] ) ||
						$accessHash == $row[ "ACCESS_HASH" ] )
				{
					$result = $this->loadFromRow( $row );
				}
			}
		}


		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: verifyPassword
	// Abstract: Verifies that this is the user's password
	// ------------------------------------------------------------------------
	public function verifyPassword( $password )
	{
		$result = false;

		// Aliases for SQL query
		$TUA = new SQL\Tables\user_accounts( );

		// Select all data about the specified user
		$query = "
		SELECT
			$TUA->user_account_id			AS USER_ACCOUNT_ID,
			$TUA->password_hash				AS PASSWORD_HASH

		FROM
			$TUA
		WHERE
			( $TUA->email_address 	= ?
		AND   $TUA->email_address 	!= '' )
		OR	( $TUA->access_hash 	= ?
		AND   $TUA->access_hash 	!= '' )
		LIMIT 1
		";

		if( SQL::query( $query, $this->emailAddress, $this->accessHash ) )
		{
			if( $row = SQL::fetch() )
			{
				// If the credentials are valid
				if( password_verify( $password, $row[ "PASSWORD_HASH" ] ) )
				{
					$result = true;
				}
			}
		}


		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: editAccountInfo
	// Abstract: Edit this user's account info
	// ------------------------------------------------------------------------
	public function editAccountInfo( $emailAddress, $firstName, $lastName )
	{
		$result = true;

		// Aliases for SQL query
		$TUA = new SQL\Tables\user_accounts( );

		$query = "
		UPDATE
			$TUA
		SET
			$TUA->email_address = ?,
			$TUA->first_name = ?,
			$TUA->last_name = ?
		WHERE
			( $TUA->access_hash 	= ?
		AND   $TUA->access_hash 	!= '' )
		LIMIT 1
		";

		if( !SQL::query( $query, $emailAddress, $firstName, $lastName, $this->accessHash ) )
		{
			$result = false;
		}
		else
		{
			$this->emailAddress = $emailAddress;
			$this->firstName = $firstName;
			$this->lastName = $lastName;
		}


		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: loadFromRow
	// Abstract: Load this instance from a result row
	// ------------------------------------------------------------------------
	public function loadFromRow( $row = null )
	{
		$result = false;

		if( $row !== null )
		{
			$this->userAccountId = $row[ "USER_ACCOUNT_ID" ];
			$this->emailAddress = $row[ "EMAIL_ADDRESS" ];
			$this->passwordHash = $row[ "PASSWORD_HASH" ];
			$this->accessHash = $row[ "ACCESS_HASH" ];
			$this->powerLevel = $row[ "POWER_LEVEL" ];
			$this->firstName = $row[ "FIRST_NAME" ];
			$this->middleName = $row[ "MIDDLE_NAME" ];
			$this->lastName = $row[ "LAST_NAME" ];
			$this->phoneNumber = $row[ "PHONE_NUMBER" ];
			$this->joinDate = $row[ "JOIN_DATE" ];
			$this->userAccountStatusID = $row[ "STATUS_ID" ];
			$this->userAccountStatus = $row[ "USER_ACCOUNT_STATUS" ];
			//$this->lastIP = $row[ "LAST_IP" ];
			//$this->lastLoginDate = $row[ "LAST_LOGIN_DATE" ];
			//$this->emailVerificationToken = $row[ "EMAIL_VERIFICATION_TOKEN" ];
			//$this->passwordChangeToken = $row[ "PASSWORD_CHANGE_TOKEN" ];
			$this->emailVerified = $row[ "EMAIL_VERIFIED" ];

			$result = true;
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: printProperties
	// Abstract: Print all the SQL table field values of this instance
	// ------------------------------------------------------------------------
	private function printProperties()
	{
		echo $this->userAccountId
		. ", "
		. $this->email
		. ", "
		. $this->passwordHash
		. ", "
		. $this->accessHash
		. ", "
		. $this->powerLevel
		. ", "
		. $this->firstName
		. ", "
		. $this->middleName
		. ", "
		. $this->lastName
		. ", "
		. $this->emailAddress
		. ", "
		. $this->phoneNumber
		. ", "
		. $this->joinDate
		. ", "
		. $this->intAddressID
		. ", "
		. $this->userAccountStatusID;
	}

	// ------------------------------------------------------------------------
	// Function: sqlSetPowerLevel
	// Abstract: Set this user's power level to the specified value
	// ------------------------------------------------------------------------
	public function sqlSetPowerLevel( $powerLevel )
	{
		$result = false;

		if( $this->loggedIn = true )
		{
			// Aliases for SQL query
			$TUA = new SQL\Tables\user_accounts( );

			$query = "
			UPDATE
				$TUA
			SET
				$TUA->power_level		= $powerLevel
			WHERE
				$TUA->user_account_id 	= $this->userAccountId
			";

			$result = SQL::query( $query );
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: sqlIsEmailVerified
	// Abstract: Check if this user's email address is verified
	// ------------------------------------------------------------------------
	public function sqlIsEmailVerified()
	{
		$result = false;

		// Aliases for SQL query
		$TUA = new SQL\Tables\user_accounts( );

		// Select all data about the specified user
		$query = "
		SELECT
			$TUA->email_verified			AS EMAIL_VERIFIED

		FROM
			$TUA
		WHERE
			( $TUA->access_hash 	= ?
		AND   $TUA->access_hash 	!= '' )
		LIMIT 1
		";

		if( SQL::query( $query, $this->accessHash ) )
		{
			if( $row = SQL::fetch() )
			{
				if( $row[ "EMAIL_VERIFIED" ] == true )
				{
					$result = true;
				}
			}
		}


		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: logout
	// Abstract: Log the current user out
	// ------------------------------------------------------------------------
	public function logout()
	{
		$this->deauthorize();

		$this->userAccountId = 0;
		$this->emailAddress = "";
		$this->password = "";
		$this->passwordHash = "";
		$this->powerLevel = 0;
		$this->firstName = "";
		$this->middleName = "";
		$this->lastName = "";
		$this->phoneNumber = "";
		$this->joinDate = "";
		$this->userAccountStatus = "";

		$this->lastIP = "";
		$this->lastLoginDate = "";

		$this->loggedIn = false;
	}

	// ------------------------------------------------------------------------
	// Function: userExists
	// Abstract: Check if a user with the specified email already exists
	// ------------------------------------------------------------------------
	static function userExists( $email )
	{
		$TUA = new SQL\Tables\user_accounts( );

		$result = false;
		$query = "SELECT COUNT( * ) AS USER_COUNT
					FROM $TUA
					WHERE $TUA->email_address = ?";

		if( SQL::query( $query, $email ) )
		{
			while( $row = SQL::fetch() )
			{
				$userCount = $row[ "USER_COUNT" ];

				if( $userCount > 0 )
				{
					$result = true;
				}
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: passwordHash
	// Abstract: Return a hash of the password
	// ------------------------------------------------------------------------
	static function passwordHash( $password, $salt = "" )
	{
		return password_hash( $password . $salt, PASSWORD_DEFAULT );
	}

	// ------------------------------------------------------------------------
	// Function: accessHash
	// Abstract: Return a hash to be used for user authentication.  Hash the email
	// 			 since it's unique to a user. Pepper is generated once,
	// 			 then disposed of.
	// ------------------------------------------------------------------------
	public function accessHash( $unique )
	{
		return password_hash( $unique, PASSWORD_DEFAULT );
	}

	// ------------------------------------------------------------------------
	// Function: emailVerificationToken
	// Abstract: Return a hash to be used for user email verification.  Hash the
	// 			 email address since it's unique to a user. Pepper is generated once,
	// 			 then disposed of.
	// ------------------------------------------------------------------------
	public function emailVerificationToken( $emailAddress = "" )
	{
		if( $emailAddress == "" )
		{
			$emailAddress = $this->emailAddress;
		}

		return password_hash( $emailAddress, PASSWORD_DEFAULT );
	}

	// ------------------------------------------------------------------------
	// Function: passwordChangeToken
	// Abstract: Return a hash to be used for user password changing.  Hash the
	// 			 email address since it's unique to a user. Pepper is generated once,
	// 			 then disposed of.
	// ------------------------------------------------------------------------
	public static function passwordChangeToken( $emailAddress = "" )
	{
		if( $emailAddress == "" )
		{
			$emailAddress = $this->emailAddress;
		}

		return password_hash( $emailAddress, PASSWORD_DEFAULT );
	}

}

?>