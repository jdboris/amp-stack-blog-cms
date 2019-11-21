<?php

include_once( "utilities.php" );
$user = BlogUser::currentUser();

if( $user->powerLevel < UserPowerLevels\MODERATOR )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

if( $user->loggedIn == false )
{
	redirect( URL_ROOT );
}

if( $user->deleteBlogPost( POST( "id" ) ) )
{
	redirect( URL_ROOT . "account?_REQUEST_LIBRARY=MODAL&modal-title=Success&modal-body=POST_DELETE_SUCCESS" );
}
?>