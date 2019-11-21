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


$frmSignup = new HTML\Form( "signup-form", "POST" );

$txtFirstName = $frmSignup->field( "first-name" );
$txtFirstName->pattern( RegEx\NO_WHITESPACE, "First name is required." );

$txtLastName = $frmSignup->field( "last-name" );
$txtLastName->pattern( RegEx\NO_WHITESPACE, "Last name is required." );

$txtEmail = $frmSignup->field( "email" );
$txtEmail->pattern( RegEx\EMAIL, "Please enter a valid email address." );

$txtConfirmedEmail = $frmSignup->field( "confirmed-email" );
$txtConfirmedEmail->match( $txtEmail, "Email addresses must match." );

$txtPassword = $frmSignup->field( "password" );
$txtPassword->pattern( RegEx\PASSWORD, "Please enter a valid password (8+ characters, alphanumeric and special)." );


if( $frmSignup->submitted == true && $frmSignup->valid == true )
{
	// If the email is taken
	if( BlogUser::userExists( $txtEmail->value ) == true )
	{
		$txtEmail->invalidMessage = "There is already an account with this email address.";
		$txtEmail->valid = false;
	}
	else if( $user->signUp( $txtEmail->value, $txtPassword->value, $txtFirstName->value, $txtLastName->value ) == true )
	{
		redirect( URL_ROOT . "?_REQUEST_LIBRARY=MODAL&modal-body=SIGNUP_SUCCESS" );
	}
	else
	{
		Modal::showWarning( "", Modal\GENERAL_ERROR );
	}
}


include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="col-md-4 ml-auto mr-auto">
		<h1 class="mb-3">Sign Up</h1>
		<form <?= $frmSignup ?> action="<?= URL_ROOT . PAGE ?>" class=" needs-validation" method="POST" novalidate>

			<div class="form-row">

				<div class="col-md-6 mb-3">
					<input <?= $txtFirstName ?> type="text" class="form-control" placeholder="First name">

				</div>
				<div class="col-md-6 mb-3">
					<input <?= $txtLastName ?> type="text" class="form-control" placeholder="Last name">

				</div>
			</div>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtEmail ?> type="email" class="form-control" placeholder="Email">

				</div>
			</div>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtConfirmedEmail ?> type="email" class="form-control" placeholder="Email (repeat)" >

				</div>
			</div>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtPassword ?> type="password" class="form-control" placeholder="Password">

				</div>
			</div>

			<button class="btn btn-primary" type="submit">Create Account</button>
		</form>
	</div>


</main>

<?php
include_once( "html/page-footer.php" );
?>
