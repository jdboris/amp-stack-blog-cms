<?php
include_once( "utilities.php" );
$user = BlogUser::currentUser();
$blog = Blog::currentBlog();

// If the user account or IP address is banned
if( $user->powerLevel == UserPowerLevels\RESTRICTED )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

if( $user->loggedIn == false )
{
	redirect( URL_ROOT );
	exit();
}

function disableForm( $txtFirstName, $txtLastName, $txtEmail, $btnSave, $txtPassword )
{
	$txtFirstName->setAttribute( "disabled" );
	$txtLastName->setAttribute( "disabled" );
	$txtEmail->setAttribute( "disabled" );

	$btnSave->setAttribute( "hidden" );
	$btnSave->setAttribute( "disabled" );
	$txtPassword->value = "";
}

$frmResendVerificationEmail = new HTML\Form( "resend-email-verification-form", "POST" );
$btnResendEmail = $frmResendVerificationEmail->button( "resending", "resend" );

if( $frmResendVerificationEmail->submitted == true )
{
	$user->requestEmailVerification();
	HTML\Modal::show( "", Modal\EMAIL_VERIFICATION );
}



$elmEditSuccess = new HTML\Element( );
$elmEditSuccess->setAttribute( "hidden" );

$frmEditAccount = new HTML\Form( "edit-account-form", "POST" );


$txtFirstName = $frmEditAccount->field( "first-name" );
$txtFirstName->pattern( RegEx\NO_WHITESPACE, "First name is required." );

$txtLastName = $frmEditAccount->field( "last-name" );
$txtLastName->pattern( RegEx\NO_WHITESPACE, "Last name is required." );

$txtEmail = $frmEditAccount->field( "email" );
$txtEmail->pattern( RegEx\EMAIL, "Please enter a valid email address." );

$txtPassword = $frmEditAccount->field( "password" );
$txtPassword->pattern( RegEx\ANYTHING, "Please enter your password to save your changes." );

$btnSave = $frmEditAccount->button( "action", "save" );
$btnEdit = $frmEditAccount->button( "action", "edit" );
$btnChangePassword = $frmEditAccount->button( "action", "change-password" );


if( $btnSave->clicked == true )
{
	if( $frmEditAccount->valid == true )
	{
		// If they entered new values
		if( $txtEmail->value != $user->emailAddress || $txtFirstName->value != $user->firstName || $txtLastName->value != $user->lastName )
		{
			if( $user->verifyPassword( $txtPassword->value ) == false )
			{
				$txtPassword->invalidMessage = "Please enter your current password.";
				$txtPassword->valid = false;
				$btnEdit->setAttribute( "hidden" );
			}
			// If they entered a new email but it's taken
			else if( $txtEmail->value != $user->emailAddress && BlogUser::userExists( $txtEmail->value ) == true )
			{
				$txtEmail->invalidMessage = "There is already an account with this email address.";
				$txtEmail->valid = false;
				$btnEdit->setAttribute( "hidden" );
			}
			else
			{
				$oldEmail = $user->emailAddress;

				if( $user->editAccountInfo( $txtEmail->value, $txtFirstName->value, $txtLastName->value ) == true )
				{
					$elmEditSuccess->removeAttribute( "hidden" );
					$txtPassword->value = "";

					if( $oldEmail != $txtEmail->value )
					{
						$user->requestEmailVerification();
					}

					disableForm( $txtFirstName, $txtLastName, $txtEmail, $btnSave, $txtPassword );
				}
				else
				{
					HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
				}
			}
		}
		else
		{
			$btnEdit->setAttribute( "hidden" );
		}
	}
	else
	{
		$btnEdit->setAttribute( "hidden" );
	}
}
else
{
	$txtFirstName->value = $user->firstName;
	$txtLastName->value = $user->lastName;
	$txtEmail->value = $user->emailAddress;

	if( $btnChangePassword->clicked == true )
	{
		if( $user->verifyPassword( $txtPassword->value ) == false )
		{
			$txtPassword->invalidMessage = "Please enter your current password.";
			$txtPassword->valid = false;
			disableForm( $txtFirstName, $txtLastName, $txtEmail, $btnSave, $txtPassword );
		}
		else if( $user->requestPasswordChange( $user->emailAddress ) )
		{
			HTML\Modal::show( "", Modal\PASSWORD_CHANGE_EMAIL );
			disableForm( $txtFirstName, $txtLastName, $txtEmail, $btnSave, $txtPassword );
		}
		else
		{
			HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
		}
	}
	else
	{
		// INITIAL PAGE LOAD
		disableForm( $txtFirstName, $txtLastName, $txtEmail, $btnSave, $txtPassword );
	}
}

$elmVerifyEmailAlert = new HTML\Element( );

if( $user->sqlIsEmailVerified() == true )
{
	$elmVerifyEmailAlert->setAttribute( "hidden" );
}

include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="row">
		<div class="col-md-5">
			<div>
				<h3 class="mb-3">Account Info</h3>

				<div <?= $elmVerifyEmailAlert ?> class="alert alert-warning">

					<form <?= $frmResendVerificationEmail ?> class="form-inline">
						Please verify your email address (<button <?= $btnResendEmail ?> type="submit" class="btn btn-link p-md-0">resend email</button>)
					</form>
				</div>
				<div <?= $elmEditSuccess ?> class="alert alert-success">
					Account updated successfully.
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>
			<div class="row">
				<div class="col">

					<form <?= $frmEditAccount ?> class="needs-validation mb-5" novalidate>

						<div class="form-row">

							<div class="col-md-6 mb-3" onclick="enableEditAccountForm( this )">
								<input <?= $txtFirstName ?> type="text" class="form-control" placeholder="First name">

							</div>
							<div class="col-md-6 mb-3" onclick="enableEditAccountForm( this )">
								<input <?= $txtLastName ?> type="text" class="form-control" placeholder="Last name">

							</div>
						</div>

						<div class="form-row">
							<div class="col-md-12 mb-3" onclick="enableEditAccountForm( this )">
								<input <?= $txtEmail ?> type="email" class="form-control" placeholder="Email">

							</div>
						</div>

						<div class="form-row">
							<div class="col-md-12 mb-3">
								<input <?= $txtPassword ?> type="password" class="form-control" name="password" placeholder="Current password">

							</div>
						</div>

						<button <?= $btnEdit ?> type="button" class="btn btn-primary float-right" id="edit-button" onclick="enableEditAccountForm( )">Edit</button>
						<button <?= $btnSave ?> type="submit" class="btn btn-primary float-right" id="save-button">Save Changes</button>

						<button <?= $btnChangePassword ?> type="submit" class="btn btn-primary" onclick="disableAccountForm( )">Change Password</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<?php
	if( $user->powerLevel >= UserPowerLevels\MODERATOR )
	{
		?>
		<div class="row">
			<div class="col">

				<h3 class="mb-3">Blog Posts <a class="btn btn-primary float-right" href="<?= URL_ROOT ?>create-or-edit-post" role="button">New Post</a></h3>

				<ul id="my-posts-list" class="list-group">
					<?php
					$pagination = new Pagination( "my-posts-page", $user->totalPosts, Blog::POSTS_PER_PAGE, GET( "page" ) );

					$pagination->page( function( ) {

						$user = BlogUser::currentUser();
						$posts = Blog::currentBlog()->getPosts( GET( "page" ), $user->emailAddress );

						foreach( $posts as $post )
						{
							?>
							<li class="list-group-item">
								<div class="h4"><a href="<?= URL_ROOT ?>post?id=<?= $post[ "BLOG_POST_ID" ] ?>"><?= $post[ "TITLE" ] ?></a></div>
								<small class="text-muted"><?= $post[ "DATE_POSTED" ] ?></small>
								<div><?= $post[ "PREVIEW" ] ?></div>
							</li>
							<?php
						}
					} );
					?>
				</ul>
			</div>
		</div>

		<div class="row">
			<div class="col pt-2">

				<div class="col d-flex justify-content-center">

					<button <?= $pagination->loadMoreButton() ?> class="btn btn-primary" type="button">Load More</button>

				</div>
			</div>
		</div>

		<?php
	}
	?>
</div>

<script>
	function enableEditAccountForm( clickedElement = null )
	{
		var form = $( "#edit-account-form" );

		$( "[name='first-name']", form ).prop( "disabled", false );
		$( "[name='last-name']", form ).prop( "disabled", false );
		$( "[name='email']", form ).prop( "disabled", false );

		$( "#save-button", form ).prop( "hidden", false );
		$( "#save-button", form ).prop( "disabled", false );

		$( "#edit-button", form ).prop( "hidden", true );

		if( clickedElement != null )
		{
			$( "input", clickedElement ).focus( );
	}

	}

	function disableAccountForm( )
	{
		var form = $( "#edit-account-form" );

		$( "[name='first-name']", form ).prop( "disabled", true );
		$( "[name='last-name']", form ).prop( "disabled", true );
		$( "[name='email']", form ).prop( "disabled", true );

		$( "#save-button", form ).prop( "hidden", true );
		$( "#save-button", form ).prop( "disabled", true );

		$( "#edit-button", form ).prop( "hidden", false );
	}
</script>

</main>

<?php
include_once( "html/page-footer.php" );
?>
