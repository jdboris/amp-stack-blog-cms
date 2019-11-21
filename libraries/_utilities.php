<?php
// -----------------------------------------------------------------------------------------
// CONSTANTS:
//
// PAGE - the name of the current page, taken from the filename
// IP_ADDRESS - The IP address of the visitor
// PROTOCOL - The protocol used to make this request
// URL_ROOT - The full URL to the root directory of this website
// ROOT - The absolute root directory path of this website
// AJAX - Whether or not the current request used AJAX
// -----------------------------------------------------------------------------------------

define( "PAGE", basename( $_SERVER[ "PHP_SELF" ], ".php" ) );

function getClientIp()
{
	$ipAddress = '';
	if( getenv( 'HTTP_CLIENT_IP' ) )
		$ipAddress = getenv( 'HTTP_CLIENT_IP' );
	else if( getenv( 'HTTP_X_FORWARDED_FOR' ) )
		$ipAddress = getenv( 'HTTP_X_FORWARDED_FOR' );
	else if( getenv( 'HTTP_X_FORWARDED' ) )
		$ipAddress = getenv( 'HTTP_X_FORWARDED' );
	else if( getenv( 'HTTP_FORWARDED_FOR' ) )
		$ipAddress = getenv( 'HTTP_FORWARDED_FOR' );
	else if( getenv( 'HTTP_FORWARDED' ) )
		$ipAddress = getenv( 'HTTP_FORWARDED' );
	else if( getenv( 'REMOTE_ADDR' ) )
		$ipAddress = getenv( 'REMOTE_ADDR' );
	else
		$ipAddress = 'UNKNOWN';
	return $ipAddress;
}

define( "IP_ADDRESS", getClientIp() );
//echo IP_ADDRESS;
// Disable website for developers
if( DEVELOPMENT == true && empty( $DEVELOPER_IP_ADDRESSES ) == false )
{
	if( in_array( IP_ADDRESS, $DEVELOPER_IP_ADDRESSES ) == false )
	{
		echo IP_ADDRESS;
		exit();
	}
}

// Get the protocol for the requested file. Dependent on the DNS
if( isset( $_SERVER[ "HTTP_X_FORWARDED_PROTO" ] ) == true )
{
	define( "PROTOCOL", strtoupper( $_SERVER[ "HTTP_X_FORWARDED_PROTO" ] ) );
}
else
{
	if( isset( $_SERVER[ "HTTPS" ] ) == true )
	{
		define( "PROTOCOL", "HTTPS" );
	}
	else
	{
		define( "PROTOCOL", "HTTP" );
	}
}

// The full URL to the root directory
define( "URL_ROOT", PROTOCOL . "://www." . DOMAIN . "/" );
define( "ROOT", $_SERVER[ "DOCUMENT_ROOT" ] . "/" . SUBROOT );
define( "URL", PROTOCOL . "://" . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ] );

// Dependencies: error-codes.php
if( file_exists( ROOT . "error-codes.php" ) == false )
{
	error_log( "ALERT: No 'error-codes.php' found." );
}
else
{
	include_once( ROOT . "error-codes.php" );
}

// If this was an AJAX request
if( ( isset( $_SERVER[ "HTTP_X_REQUESTED_WITH" ] ) && $_SERVER[ "HTTP_X_REQUESTED_WITH" ] == "XMLHttpRequest" ) ||
		( POST( "HTTP_X_REQUESTED_WITH" ) !== null && POST( "HTTP_X_REQUESTED_WITH" ) == "XMLHttpRequest" ) ||
		( GET( "HTTP_X_REQUESTED_WITH" ) !== null && GET( "HTTP_X_REQUESTED_WITH" ) == "XMLHttpRequest" ) )
{
	// Define the AJAX request flag
	define( "AJAX", true );
}
else
{
	// Define the AJAX request flag
	define( "AJAX", false );
}

class JSONResponse
{

	// -----------------------------------------------------------------------------------------
	// Function: __get
	// Abstract: Get the specified property
	// -----------------------------------------------------------------------------------------
	public function __get( $property )
	{
		if( isset( $this->strProperty ) == true )
		{
			return $this->$property;
		}
		else
		{
			return "";
		}
	}

	// -----------------------------------------------------------------------------------------
	// Function: __destruct
	// Abstract: Echo this object encoded as a JSON string
	// -----------------------------------------------------------------------------------------
	public function __destruct()
	{

		// If the headers were already sent, you're trying to mix HTML and JSON
		if( headers_sent() == true )
		{
			error_log( "Error: Can't set Content-Type header for JSON output; headers already sent; user IP: " . IP_ADDRESS );
		}
		else
		{
			if( AJAX == true )
			{
				$aProperties = get_object_vars( $this );

				// If this object has properties
				if( empty( $aProperties ) == false )
				{
					header( "Content-type: application/json; charset=utf-8" );
					echo json_encode( $this );
				}
			}
		}
	}

}

// Static cookie class to get/set cookies
// WARNING: MUST USE www IN ORDER FOR COOKIES TO WORK IN ALL BROWSERS
class COOKIE
{

	// -----------------------------------------------------------------------------------------
	// Function: set
	// Abstract: Sets the specified cookie to the specified value
	// -----------------------------------------------------------------------------------------
	static function set( $name, $value = "", $expireTime = 0, $HTTPOnly = true )
	{
		if( headers_sent() == true )
		{
			error_log( "Can't set cookie '$name' to value '$value'; headers already sent; user IP: " . IP_ADDRESS );
			error_log( print_r( debug_backtrace(), true ) );
		}
		else
		{
			if( PROTOCOL == "HTTPS" )
				$secure = true;
			else
				$secure = false;

			// Default to one week
			if( $expireTime == 0 || $expireTime == null )
				$expireTime = time() + 604800;

			if( substr( DOMAIN, 0, 9 ) == "localhost" )
				setcookie( $name, $value, $expireTime );
			else
			{
				//error_log( $name . "\n" . $value . "\n" . $expireTime . "\n" . "/" . "\n" . "." . urlencode( DOMAIN ) . "\n" . $secure . "\n" . $HTTPOnly );
				setcookie( $name, $value, $expireTime, "/", "." . urlencode( DOMAIN ), $secure, $HTTPOnly );
			}
			$_COOKIE[ $name ] = $value;
		}
	}

	// -----------------------------------------------------------------------------------------
	// Function: get
	// Abstract: Get the value of the specified cookie
	// -----------------------------------------------------------------------------------------
	static function get( $name )
	{
		if( isset( $_COOKIE[ $name ] ) == true )
		{
			return $_COOKIE[ $name ];
		}
		else
		{
			return null;
		}
	}

	// -----------------------------------------------------------------------------------------
	// Function: delete
	// Abstract: Deletes the specified cookie
	// -----------------------------------------------------------------------------------------
	static function delete( $name )
	{
		unset( $_COOKIE[ $name ] );
		COOKIE::set( $name, null, -1 );
	}

}

// Static session class to get/set session variables
class SESSION
{

	// -----------------------------------------------------------------------------------------
	// Function: set
	// Abstract: Sets the specified session variable to the specified value
	// -----------------------------------------------------------------------------------------
	static function set( $name, $value = "" )
	{
		$_SESSION[ $name ] = $value;
	}

	// -----------------------------------------------------------------------------------------
	// Function: get
	// Abstract: Get the value of the specified session variable
	// -----------------------------------------------------------------------------------------
	static function get( $name )
	{
		if( isset( $_SESSION[ $name ] ) == true )
		{
			return $_SESSION[ $name ];
		}
		else
		{
			return null;
		}
	}

	// -----------------------------------------------------------------------------------------
	// Function: delete
	// Abstract: Deletes the specified session variable
	// -----------------------------------------------------------------------------------------
	static function delete( $name )
	{
		unset( $_SESSION[ $name ] );
	}

}

class Timer
{

	private static $rustart = 0;
	private static $ru = 0;

	// -----------------------------------------------------------------------------------------
	// Function: start
	// Abstract: Start the timer
	// -----------------------------------------------------------------------------------------
	public static function start()
	{
		self::$rustart = getrusage();
	}

	// -----------------------------------------------------------------------------------------
	// Function: stop
	// Abstract: Stop the timer
	// -----------------------------------------------------------------------------------------
	public static function stop()
	{
		self::$ru = getrusage();
		return self::rutime( self::$ru, self::$rustart, "utime" );
	}

	private static function rutime( $ru, $rus, $index )
	{
		return ($ru[ "ru_$index.tv_sec" ] * 1000 + intval( $ru[ "ru_$index.tv_usec" ] / 1000 )) - ($rus[ "ru_$index.tv_sec" ] * 1000 + intval( $rus[ "ru_$index.tv_usec" ] / 1000 ));
	}

	// -----------------------------------------------------------------------------------------
	// Function: utime
	// Abstract: Return the computation time of the previously-timed code in milliseconds
	// -----------------------------------------------------------------------------------------
	public static function utime()
	{
		return self::rutime( self::$ru, self::$rustart, "utime" );
	}

	// -----------------------------------------------------------------------------------------
	// Function: stime
	// Abstract: Return the system call time of the previously-timed code in milliseconds
	// -----------------------------------------------------------------------------------------
	public static function stime()
	{
		return self::rutime( self::$ru, self::$rustart, "stime" );
	}

}

// -----------------------------------------------------------------------------------------
// Function: timeSince
// Abstract: Returns a string representing how long ago the specified date string was
// -----------------------------------------------------------------------------------------
function timeSince( $dateString, $format = "M j, Y g:i A" )
{
	$date = \DateTime::createFromFormat( $format, $dateString );
	if( !$date )
	{
		error_log( "Error: DateTime::createFromFormat( ) failed." );
	}
	else
	{
		$interval = $date->diff( new \DateTime( "now" ) );

		if( $interval->y > 1 )
			return $interval->format( "%y years ago" );
		if( $interval->y > 0 )
			return $interval->format( "%y year ago" );
		if( $interval->m > 1 )
			return $interval->format( "%m months ago" );
		if( $interval->m > 0 )
			return $interval->format( "%m month ago" );
		if( $interval->d > 1 )
			return $interval->format( "%d days ago" );
		if( $interval->d > 0 )
			return $interval->format( "%d day ago" );
		if( $interval->h > 1 )
			return $interval->format( "%h hours ago" );
		if( $interval->h > 0 )
			return $interval->format( "%h hour ago" );
		if( $interval->i > 1 )
			return $interval->format( "%i minutes ago" );
		if( $interval->i > 0 )
			return $interval->format( "%i minute ago" );
		if( $interval->s > 1 )
			return $interval->format( "%s seconds ago" );
		if( $interval->s > 0 )
			return $interval->format( "%s second ago" );

		return "Just now";
	}
}

// -----------------------------------------------------------------------------------------
// Function: includeGet
// Abstract: Includes the specified file and returns the contents instead of printing
// -----------------------------------------------------------------------------------------
function includeGet( $file )
{
	ob_start();
	include( $file );
	return ob_get_clean();
}

// -----------------------------------------------------------------------------------------
// Function: includeVirtual
// Abstract: Includes a "virtual file", which is a script that was stored as a session string.
//			 If a callback is provided, the virtual file becomes the body of the callback
// -----------------------------------------------------------------------------------------
function includeVirtual( $virtualFileName, Closure $callback = null )
{
	if( $callback != null )
	{
		$func = new ReflectionFunction( $callback );
		$filename = $func->getFileName();
		$startLine = $func->getStartLine();
		$endLine = $func->getEndLine();
		$length = $endLine - $startLine - 1;

		$source = file( $filename );
		$body = implode( "", array_slice( $source, $startLine, $length ) );

		// In case there are PHP tags in the callback body
		$body = ltrim( $body, "<?php" );
		$body = rtrim( $body, "?>" );

		$virtualFiles = SESSION::get( "_LIBRARIES::VIRTUAL_FILES" );

		if( $virtualFiles == null )
		{
			$virtualFiles = [ $virtualFileName => $body ];
		}
		else
		{
			$virtualFiles[ $virtualFileName ] = $body;
		}

		SESSION::set( "_LIBRARIES::VIRTUAL_FILES", $virtualFiles );

		return $callback();
	}
	else
	{
		$virtualFiles = SESSION::get( "_LIBRARIES::VIRTUAL_FILES" );

		return eval( $virtualFiles[ $virtualFileName ] );
	}
}

// -----------------------------------------------------------------------------------------
// Function: embedCSS
// Abstract: Prints a style tag with the css from the specified file
// -----------------------------------------------------------------------------------------
function embedCSS( $file )
{
	?>
	<style>
	<?= file_get_contents( $file, true ) ?>
	</style>
	<?php
}

// -----------------------------------------------------------------------------------------
// Function: propertyArray
// Abstract: Returns an array with values from the specified property from the specified
// 			 array of objects.
// -----------------------------------------------------------------------------------------
function propertyArray( $objects, $property )
{
	$values = array();

	// Loop through every object
	foreach( $objects as $object )
	{
		// Push the value into the array
		array_push( $values, $object->$property );
	}

	return $values;
}

// -----------------------------------------------------------------------------------------
// Function: relativePath
// Abstract: Returns a relative path to the specified (absolute url) file
// -----------------------------------------------------------------------------------------
function relativePath( $absoluteURL )
{
	$relativePath = "";

	// Loop through each directory in the self path
	for( $index = 0; $index < substr_count( $_SERVER[ "PHP_SELF" ], '/' ); $index += 1 )
	{
		$relativePath .= "../";
	}

	$relativePath .= $absoluteURL;

	return $relativePath;
}

// -----------------------------------------------------------------------------------------
// Function: POST
// Abstract: Get the specified POST variable if it exists
// -----------------------------------------------------------------------------------------
function POST( $key )
{
	if( isset( $_POST[ $key ] ) == true )
	{
		return $_POST[ $key ];
	}
	else
	{
		return null;
	}
}

// -----------------------------------------------------------------------------------------
// Function: GET
// Abstract: Get the specified GET variable if it exists
// -----------------------------------------------------------------------------------------
function GET( $key )
{
	if( isset( $_GET[ $key ] ) == true )
	{
		return $_GET[ $key ];
	}
	else
	{
		return null;
	}
}

// -----------------------------------------------------------------------------------------
// Function: printJS
// Abstract: Wrap the specified javascript code in script tags
// -----------------------------------------------------------------------------------------
function printJS( $javascript )
{
	echo toJS( $javascript );
}

// -----------------------------------------------------------------------------------------
// Function: toJS
// Abstract: Wrap the specified javascript code in script tags.
// -----------------------------------------------------------------------------------------
function toJS( $javascript )
{
	return "
	<script>

	$javascript

	</script>
	";
}

// -----------------------------------------------------------------------------------------
// Function: Redirect
// Abstract: Redirect the user to the specified location either in javascript or PHP
// -----------------------------------------------------------------------------------------
function redirect( $location )
{
	// If the headers were already sent or if this file was loaded with AJAX
	if( headers_sent() == true || AJAX == true )
	{
		JSRedirect( $location );
	}
	else
	{
		PHPRedirect( $location );
	}
}

// -----------------------------------------------------------------------------------------
// Function: JSRedirect
// Abstract: Redirect the user to the specified location in javascript
// -----------------------------------------------------------------------------------------
function JSRedirect( $location )
{
	echo toJS( "window.location.href = '$location';" );
}

// -----------------------------------------------------------------------------------------
// Function: PHPRedirect
// Abstract: Redirect the user to the specified location in PHP
// -----------------------------------------------------------------------------------------
function PHPRedirect( $location )
{
	header( "Location: $location" );
	exit();
}

// -----------------------------------------------------------------------------------------
// Function: sendEmail
// Abstract: Send an email
// -----------------------------------------------------------------------------------------
function sendEmail( $targetAddress, $subject, $body, $headers = "" )
{
	if( $headers == "" )
	{
		$headers = "From: \"" . WEBSITE_TITLE . "\" <" . SENDER_EMAIL . ">\r\n";
		$headers .= "Reply-To: \"" . WEBSITE_TITLE . "\" <" . ADMINISTRATOR_EMAIL . ">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	}

	if( !mail( $targetAddress, $subject, $body, $headers ) )
	{
		error_log( error_get_last()[ "message" ] );
		return false;
	}

	return true;
}

// -----------------------------------------------------------------------------------------
// Includes
// -----------------------------------------------------------------------------------------

ini_set( "include_path", get_include_path() . PATH_SEPARATOR . rtrim( ROOT, "/" ) );

// Buffer output in this file to move it between the <head> tags
class HeadBuffer
{

	private static $output = "";

	public static function start()
	{
		return ob_start( function( $output ) {
			HeadBuffer::add( $output );
		} );
	}

	public static function end()
	{
		ob_end_clean();
	}

	public static function get()
	{
		return self::$output;
	}

	public static function add( $output )
	{
		self::$output .= $output;
	}

}

// Buffer output to move it to the bottom of the body
class FooterBuffer
{

	private static $output = "";
	public static $onGetCallback = null;

	public static function start()
	{
		return ob_start( function( $output ) {
			FooterBuffer::add( $output );
		} );
	}

	public static function end()
	{
		ob_end_clean();
	}

	public static function onGet( $callback )
	{
		self::$onGetCallback = $callback;
	}

	public static function get()
	{
		(self::$onGetCallback)();
		return self::$output;
	}

	public static function add( $output )
	{
		self::$output .= $output;
	}

}

if( AJAX == false )
{
	// Start output buffering to move any output from these files into the <head> tags
	HeadBuffer::start();
}

// -----------------------------------------------------------------------------------------
// JavaScript Headers
// -----------------------------------------------------------------------------------------

include_once( "RegEx.php" );
include_once( "UserPowerLevels.php" );
include_once( "Modal.php" );
include_once( "flags.php" );

require_once( "libraries/_html.php" );


ini_set( "session.gc_maxlifetime", 7776000 );
ini_set( "session.cookie_lifetime", 7776000 );
// If the session is not started
if( session_id() == "" || isset( $_SESSION ) == false )
{
	session_start();
}

// If forms on this page can resubmit on refresh
if( ALLOW_FORM_RESUBMISSIONS == false && AJAX == false )
{

	// If resubmission has not been prevented already
	if( !empty( $_POST ) && !isset( $_SESSION[ "PREVENTING_RESUBMISSION" ] ) )
	{
		// Store this form's data in $_SESSION then reload the page
		$_SESSION[ "DATA_FROM_FORM" ] = $_POST;
		$_SESSION[ "PREVENTING_RESUBMISSION" ] = true;
		redirect( URL );
	}

	// If this was a reload to prevent resubmission
	if( empty( $_POST ) && isset( $_SESSION[ "DATA_FROM_FORM" ] ) )
	{
		// Return the data to $_POST
		$_POST = $_SESSION[ "DATA_FROM_FORM" ];
		unset( $_SESSION[ "DATA_FROM_FORM" ] );
	}
}

function shutdown()
{
	if( isset( $_SESSION[ "PREVENTING_RESUBMISSION" ] ) && !isset( $_SESSION[ "DATA_FROM_FORM" ] ) )
		unset( $_SESSION[ "PREVENTING_RESUBMISSION" ] );
}

register_shutdown_function( "shutdown" );


require_once( "libraries/_pdo.php" );
include_once( "sql/database-schema.php" );
require_once( "libraries/_user.php" );

if( WEBSITE_TYPE == WebsiteTypes\BLOG )
{
	require_once( "libraries/_blog.php" );
	require_once( "libraries/_blog-user.php" );
}

if( AJAX == true )
{
	ob_start();
}

require_once( "libraries/_pagination.php" );
require_once( "libraries/_request-libraries.php");

if( AJAX == true )
{
	ob_clean();
}

if( AJAX == false )
{
	HeadBuffer::end();
}
?>
