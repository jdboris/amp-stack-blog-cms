<?php
include_once( "utilities.php" );
$user = BlogUser::currentUser();
$blog = Blog::currentBlog();

// If the user is not an admin
if( $user->powerLevel < UserPowerLevels\ADMINISTRATOR )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

if( $user->loggedIn == false )
{
	redirect( URL_ROOT );
	exit();
}

$form = new HTML\Form( "users-search-form" );
$txtUsersSearch = $form->field( "users-search" );

include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="row">
		<div class="col">

			<div class="row">
				<div class="col">
					<h3 class="mb-3">Users
					</h3>
				</div>
				<div class="col">
					<div class="float-right">
						<form class="form-inline">
							<input <?= $txtUsersSearch ?> class="form-control" type="text" style="vertical-align: middle;" placeholder="email, name, etc..." />
							<button class="btn btn-secondary" type="submit">Search</button>
						</form>
					</div>
				</div>
			</div>
			<div class="row" id="user-change-response">
				<div class="col">
				</div>
			</div>
			<div class="row">
				<div class="col">
					<table class="table">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Email Address</th>
								<th scope="col">Name</th>
								<th scope="col">Registration Date</th>
								<th scope="col">Power Level</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$pagination = new Pagination( "admin-view-users-page", $blog->totalUsers, Blog::USERS_PER_PAGE, GET( "page" ) );

							$pagination->page( function( ) {

								$users = Blog::currentBlog()->userSearch( GET( "page" ), GET( "users-search" ) );

								foreach( $users as $user )
								{
									?>
									<tr data-user-id="<?= $user[ "USER_ID" ] ?>">
										<th scope="row"><?= $user[ "USER_ID" ] ?></th>
										<td><?= $user[ "EMAIL_ADDRESS" ] ?></td>
										<td><?= $user[ "FIRST_NAME" ] . " " . $user[ "MIDDLE_NAME" ] . " " . $user[ "LAST_NAME" ] ?></td>
										<td><?= $user[ "JOIN_DATE" ] ?></td>
										<td>
											<select class="power-level" onchange="setPowerLevel( <?= $user[ "USER_ID" ] ?>, this.value )">
												<option value="<?= UserPowerLevels\RESTRICTED ?>" <?= $user[ "POWER_LEVEL" ] == UserPowerLevels\RESTRICTED ? "selected" : "" ?>>RESTRICTED</option>
												<option value="<?= UserPowerLevels\NORMAL ?>" <?= $user[ "POWER_LEVEL" ] == UserPowerLevels\NORMAL ? "selected" : "" ?>>NORMAL</option>
												<option value="<?= UserPowerLevels\MODERATOR ?>" <?= $user[ "POWER_LEVEL" ] == UserPowerLevels\MODERATOR ? "selected" : "" ?>>MODERATOR</option>
												<option value="<?= UserPowerLevels\ADMINISTRATOR ?>" <?= $user[ "POWER_LEVEL" ] == UserPowerLevels\ADMINISTRATOR ? "selected" : "" ?>>ADMINISTRATOR</option>
												<option value="<?= UserPowerLevels\DEVELOPER ?>" <?= $user[ "POWER_LEVEL" ] == UserPowerLevels\DEVELOPER ? "selected" : "" ?>>DEVELOPER</option>
											</select>
										</td>
									</tr>
									<?php
								}
							} );
							?>
						</tbody>
					</table>
				</div>
			</div>
			<script>
				function setPowerLevel( userID, powerLevel )
				{
					$( "[data-user-id=" + userID + "] .power-level" ).prop( "disabled", true );
					$.post( "libraries/set-power-level", { "user-id": userID, "power-level": powerLevel }, function( response ) {
						$( "#user-change-response" ).html( response );
						$( "[data-user-id=" + userID + "] .power-level" ).prop( "disabled", false );
					} );
				}
			</script>
		</div>
	</div>

	<div class="row">
		<div class="col pt-2">

			<div class="col d-flex justify-content-center">

				<button <?= $pagination->loadMoreButton() ?> class="btn btn-primary" type="button">Load More</button>

			</div>
		</div>
	</div>

</div>


</main>

<?php
include_once( "html/page-footer.php" );
?>
