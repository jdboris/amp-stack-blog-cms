<?php
include_once( "utilities.php" );

$user = BlogUser::currentUser();

// If the user account or IP address is banned
if( $user->powerLevel == UserPowerLevels\RESTRICTED )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

if( $user->loggedIn == true )
{
	redirect( URL_ROOT );
	exit();
}

$frmLogin = new HTML\Form( "login-form", "POST" );


$txtEmail = $frmLogin->field( "email" );
$txtEmail->pattern( RegEx\EMAIL, "Please enter a valid email address." );

$txtPassword = $frmLogin->field( "password" );
$txtPassword->pattern( RegEx\PASSWORD, "Please enter a valid password (8+ characters, alphanumeric and special)." );

if( $frmLogin->submitted == true && $frmLogin->valid == true )
{
	error_log( "logging in: " . $txtEmail->value);
	if( $user->login( $txtEmail->value, $txtPassword->value ) == true )
	{
		error_log( "logged in: TRUE");
		redirect( URL_ROOT );
	}
	else
	{
		error_log( "logged in: FALSE");
		$txtEmail->valid = false;
		$txtPassword->valid = false;
		$txtEmail->invalidMessage = "";
		$txtPassword->invalidMessage = "Invalid email/password.";
	}
}


include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="col-md-4 ml-auto mr-auto">
		<h1 class="mb-3">Log In</h1>
		<form <?= $frmLogin ?> class="needs-validation" method="POST" novalidate>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtEmail ?> type="email" class="form-control" placeholder="Email">

				</div>
			</div>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtPassword ?> type="password" class="form-control"placeholder="Password">
					<small class="text-muted"><a href="<?= URL_ROOT ?>forgot-password">Forgot your password?</a></small>
				</div>
			</div>

			<button class="btn btn-primary" type="submit">Log In</button>
		</form>
	</div>

</main>

<?php
include_once( "html/page-footer.php" );
?>
