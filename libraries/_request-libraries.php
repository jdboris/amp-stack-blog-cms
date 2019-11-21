<?php

include_once( "utilities.php" );

if( empty( $_REQUEST[ "_REQUEST_LIBRARY" ] ) == false )
{
	$library = $_REQUEST[ "_REQUEST_LIBRARY" ];

	if( $library == "VIRTUAL_FILE" )
	{
		if( AJAX == true )
		{
			includeVirtual( $_REQUEST[ "file-name" ] );
			exit();
		}
	}


	if( $library == "MODAL" )
	{
		$modalClasses = "";
		if( isset( $_REQUEST[ "modal-classes" ] ) )
		{
			$modalClasses = $_REQUEST[ "modal-classes" ];
		}

		HTML\Modal::show( $_REQUEST[ "modal-title" ], constant( "Modal\\" . $_REQUEST[ "modal-body" ] ), $modalClasses );
		?>
		<script>removeFromQueryString( "_REQUEST_LIBRARY", "modal-title", "modal-body" )</script>
		<?php

	}
}
?>