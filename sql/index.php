<?php
include_once( dirname( __DIR__ ) . "/utilities.php" );

if( DEVELOPMENT != true )
{
	$user = BlogUser::currentUser();

	// If the user account is not a developer
	if( $user->powerLevel < UserPowerLevels\DEVELOPER )
	{
		header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
		exit();
	}
}

$schemaCreationForm = new HTML\Form( "schema-creation-form", "POST" );
$schemaModelGenerationForm = new HTML\Form( "schema-model-generation-form", "POST" );
?>

<?php include_once( "../html/page-header.php" ) ?>

<script>
	function confirmSubmit( )
	{
		var blnResult = false;
		var intRandomNumber = Math.floor( ( Math.random( ) * 1000000 ) + 1 );

		var strInput = window.prompt( "Are you sure?\nEnter " + intRandomNumber + " to confirm." );

		if( Number( strInput ) == intRandomNumber )
		{
			blnResult = true;
		}

		return blnResult;
	}
</script>

<main class="container py-5 d-flex justify-content-center align-items-center flex-column">

	<?php
	if( POST( "create-schema" ) !== null )
	{
		SQL::createSchema();
		SQL::showSchema();
	}

	if( POST( "generate-schema-models" ) !== null )
	{
		SQL::generateSchemaModels();
		SQL::showSchema();
	}

	if( GET( "show-schema" ) !== null )
	{
		SQL::showSchema();
	}
	?>

	<form <?= $schemaCreationForm ?> onsubmit="return confirmSubmit( )">
		<input name="create-schema" type="submit" value="Create Schema" />
	</form>

	<form <?= $schemaModelGenerationForm ?> onsubmit="return confirmSubmit( )">
		<input name="generate-schema-models" type="submit" value="Generate Schema Models" />
	</form>

	<form>
		<input name="show-schema" type="submit" value="Show Schema" />
	</form>

</main>


<?php include_once( "../html/page-footer.php" ) ?>