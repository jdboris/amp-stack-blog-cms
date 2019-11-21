<?php
include_once( "utilities.php" );

$token = GET( "token" );

$frmPasswordChange = new HTML\Form( "password-change-form", "POST" );

$txtPassword = $frmPasswordChange->field( "password" );
$txtPassword->pattern( RegEx\PASSWORD, "Please enter a valid password (8+ characters, alphanumeric and special)." );

include_once( "html/page-header.php" );
?>
<main class="container py-5">

	<div class="col-md-4 ml-auto mr-auto">
		<h1 class="mb-3">Password Change</h1>

		<?php
		if( $token == null )
		{
			$frmPasswordChange->setAttribute( "hidden" );
			?>
			<div class="alert alert-warning d-flex justify-content-center">Something went wrong.</div>
			<?php
		}
		else if( $frmPasswordChange->submitted == true && $frmPasswordChange->valid == true )
		{
			$frmPasswordChange->setAttribute( "hidden" );

			if( BlogUser::changePassword( $token, $txtPassword->value ) == false )
			{
				?>
				<div class="alert alert-warning d-flex justify-content-center">Sorry, this link is invalid or expired.</div>
				<?php
			}
			else
			{
				?>
				<div class="alert alert-success d-flex justify-content-center">Password change successful!</div>
				<?php
			}
		}
		?>

		<form <?= $frmPasswordChange ?> class=" needs-validation" method="POST" novalidate>

			<div class="form-row">
				<div class="col-md-12 mb-3">
					<input <?= $txtPassword ?> type="password" class="form-control"placeholder="New password">

				</div>
			</div>

			<button class="btn btn-primary" type="submit">Change Password</button>
		</form>
	</div>

</main>
<?php
include_once( "html/page-footer.php" );
?>
