
<!DOCTYPE html>
<html lang="en">

	<head>

		<title><?= WEBSITE_TITLE ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- FAVICON -->
		<link rel="apple-touch-icon" sizes="57x57" href="images/favicons/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="images/favicons/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="images/favicons/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="images/favicons/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="images/favicons/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="images/favicons/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="images/favicons/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="images/favicons/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="images/favicons/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="images/favicons/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="images/favicons/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
		<link rel="manifest" href="images/favicons/manifest.json">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="images/favicons/ms-icon-144x144.png">
		<meta name="theme-color" content="#ffffff">

		<!--
		<link rel="shortcut icon" href="<?= URL_ROOT . IMAGE_PATH ?>favicon.ico" type="image/x-icon">
		<link rel="icon" href="<?= URL_ROOT . IMAGE_PATH ?>favicon.ico" type="image/x-icon">
		-->

		<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/solid.js" integrity="sha384-+Ga2s7YBbhOD6nie0DzrZpJes+b2K1xkpKxTFFcx59QmVPaSA8c7pycsNaFwUK6l" crossorigin="anonymous"></script>
		<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/brands.js" integrity="sha384-sCI3dTBIJuqT6AwL++zH7qL8ZdKaHpxU43dDt9SyOzimtQ9eyRhkG3B7KMl6AO19" crossorigin="anonymous"></script>
		<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/fontawesome.js" integrity="sha384-7ox8Q2yzO/uWircfojVuCQOZl+ZZBg2D2J5nkpLqzH1HY0C1dHlTKIbpRz/LG23c" crossorigin="anonymous"></script>
		<!--
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
		-->
		<link rel="stylesheet" href="<?= URL_ROOT ?>css/custom.css">
		<script src="<?= URL_ROOT ?>tether-1.3.3/tether.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

		<link href="<?= URL_ROOT ?>summernote/summernote-bs4.css" rel="stylesheet">
		<script src="<?= URL_ROOT ?>summernote/summernote-bs4.min.js"></script>

		<script src="<?= URL_ROOT . JAVASCRIPT_PATH ?>_utilities.js"></script>

		<?= HeadBuffer::get() ?>

	</head>

	<body class="<?= PAGE ?>-page">

		<header>
			<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
				<div class="container px-0">
					<div class="col-md-1 text-center">
						<a class="navbar-brand text-secondary" href="<?= URL_ROOT ?>">GH</a>
					</div>

					<div class="col-md-11">
						<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
							<span class="navbar-toggler-icon"></span>
						</button>

						<div class="collapse navbar-collapse" id="navbarSupportedContent">
							<ul class="navbar-nav mr-auto">
								<li class="nav-item">
									<a class="nav-link <?= ( PAGE == "index" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>">Home <span class="sr-only">(current)</span></a>
								</li>
								<li class="nav-item">
									<a class="nav-link <?= ( PAGE == "about" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>about">About</a>
								</li>
								<?php
								if( $user->loggedIn == false )
								{
									?>
									<li class="nav-item">
										<a class="nav-link <?= ( PAGE == "signup" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>signup">Sign Up</a>
									</li>
									<li class="nav-item">
										<a class="nav-link <?= ( PAGE == "login" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>login">Log In</a>
									</li>
									<?php
								}
								else
								{
									?>
									<li class="nav-item <?= ( PAGE == "account" ? "active" : "" ) ?>">
										<a class="nav-link <?= ( PAGE == "account" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>account">Account</a>
									</li>

									<?php
									if( $user->powerLevel >= UserPowerLevels\ADMINISTRATOR )
									{
										?>

										<li class="nav-item <?= ( PAGE == "users" ? "active" : "" ) ?>">
											<a class="nav-link <?= ( PAGE == "users" ? "active" : "" ) ?>" href="<?= URL_ROOT ?>users">Users</a>
										</li>
										<?php
									}
									?>

									<li class="nav-item">
										<a class="nav-link" href="<?= URL_ROOT ?>logout">Log Out</a>
									</li>
									<?php
								}
								?>
								<!--
								<li class="nav-item">
									<a class="nav-link" href="#">Link</a>
								</li>
								<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										Dropdown
									</a>
									<div class="dropdown-menu" aria-labelledby="navbarDropdown">
										<a class="dropdown-item" href="#">Action</a>
										<a class="dropdown-item" href="#">Another action</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item" href="#">Something else here</a>
									</div>
								</li>
								<li class="nav-item">
									<a class="nav-link disabled" href="#">Disabled</a>
								</li>
								-->
							</ul>
							<form <?= $searchForm ?> action="<?= URL_ROOT ?>search" class="form-inline my-2 my-lg-0 col-md-5 justify-content-end">
								<input <?= $txtSearch ?> class="form-control mr-sm-2 col-md-7 border-secondary" type="search" placeholder="Search" aria-label="Search">
								<button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>
							</form>
						</div>
					</div>
				</div>

			</nav>
		</header>
