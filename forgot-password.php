<?php
include_once( "utilities.php" );

$user = BlogUser::currentUser();

// If the user account or IP address is banned
if( $user->powerLevel == UserPowerLevels\RESTRICTED )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

$frmChangePassword = new HTML\Form( "change-password-form", "POST" );

$txtEmail = $frmChangePassword->field( "email" );
$txtEmail->pattern( RegEx\EMAIL, "Please enter a valid email address." );

if( $frmChangePassword->submitted == true && $frmChangePassword->valid == true )
{
	if( $user->requestPasswordChange( $txtEmail->value ) )
	{
		HTML\Modal::show( "", Modal\PASSWORD_CHANGE_EMAIL );
	}
	else
	{
		HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
	}
}

include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="col-md-4 ml-auto mr-auto">

		<h1 class="mb-3">Reset Password</h1>

		<form <?= $frmChangePassword ?> class="needs-validation" action="<?= URL_ROOT ?>forgot-password" method="POST" novalidate>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtEmail ?> type="email" class="form-control" placeholder="Email">

				</div>
			</div>

			<button type="submit" class="btn btn-primary">Reset Password</button>
		</form>
	</div>


</main>

<?php
include_once( "html/page-footer.php" );
?>
