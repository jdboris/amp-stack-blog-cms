<?php
include_once( "utilities.php" );

$token = GET( "token" );

include_once( "html/page-header.php" );
?>
<main class="container d-flex align-items-center justify-content-center" style="min-height:200px">
	<?php
	if( $token == null )
	{
		?>
		<span class="alert alert-warning">Something went wrong.</span>
		<?php
	}
	else if( BlogUser::verifyEmailAddress( $token ) == false )
	{
		?>
		<span class="alert alert-warning">Sorry, this link is invalid or expired.</span>
		<?php
	}
	else
	{
		?>
		<span class="alert alert-success">Email verification successful!</span>
		<?php
	}
	?>
</main>
<?php
include_once( "html/page-footer.php" );
?>
