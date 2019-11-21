<?php

include_once( dirname( __DIR__ ) . "/utilities.php" );
$user = User::currentUser();

if( $user->powerLevel >= UserPowerLevels\ADMINISTRATOR )
{
	if( POST( "user-id" ) == null || POST( "power-level" ) == null )
	{
		HTML\Modal::showDanger( "Error", "Failed to change the user's power level." );
	}
	else
	{
		if( User::setUserPowerLevel( POST( "user-id" ), POST( "power-level" ) ) )
		{
			HTML\Modal::show( "Success", "User's power level changed successfully." );
		}
	}
}
?>