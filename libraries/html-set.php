<?php

include_once( dirname( __DIR__ ) . "/utilities.php" );

$user = BlogUser::currentUser();

if( $user->powerLevel >= UserPowerLevels\MODERATOR )
{
	if( POST( "key" ) !== null && POST( "content" ) !== null )
	{
		HTML\set( POST( "key" ), POST( "content" ) );
	}
}
?>