<?php

// TODO: Define bit flags to be used in this project (if any),
// 		 and include() this file between <script> tags to use the flags on the client
// Examples:
// defineFlag( "SHOW_ACTIVE" );
// defineFlag( "SHOW_IMAGE" );
// defineFlag( "SHOW_PRICE" );
// defineFlag( "SHOW_CHECKBOX" );

class FLAGS
{

	public static $server = true;
	public static $client = false;
	public static $count = 0;

}

// If there are no other included files
if( count( get_included_files() ) == 1 )
{
	FLAGS::$server = false;
	FLAGS::$client = true;
}

// -----------------------------------------------------------------------------------------
// Global client/server function argument flags
// -----------------------------------------------------------------------------------------
function defineFlag( $name )
{
	if( FLAGS::$server == true )
	{
		define( $name, 1 << FLAGS::$count );
		FLAGS::$count += 1;
	}
	else
	{
		echo "var $name = 1 << " . FLAGS::$count . ";";
	}
}

?>