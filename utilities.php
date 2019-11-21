<?php

// TODO: Set the required options
// TODO: Call HeadBuffer::get( ) in the <head> tags
// TODO: Call FooterBuffer::get( ) at the end of the body tags
/*
  WEBSITE_TITLE
  The title/name of this website

  DOMAIN
  The domain name of this website

  ADMINISTRATOR_EMAIL
  The email address of the administrator of this website
  NOTE: If this address is not hosted on the same domain as this website, sending emails may fail

  SENDER_EMAIL
  The address sending emails from this host
  NOTE: NOT the administrator email. Check with your host or "track" sent emails in cPanel

  JAVASCRIPT_PATH
  The relative path to javascript files on this server

  CSS_PATH
  The relative path to css files on this server

  IMAGE_PATH
  The relative path to images on this server

  ERROR_CODES
  Whether or not to use codes to represent errors on this website. If true,
  expects an "error-codes.php" in this directory.

  DATABASE_HOST
  The host of your database(if any), usually localhost

  DATABASE_USER
  The username of your database(if any) user account

  DATABASE_PASSWORD
  The password of your database(if any) user account


  DATABASE_NAME
  The name of your database(if any)

  SUBROOT
  The subdirectory of this website (if not the root)

  ALLOW_FORM_RESUBMISSIONS
  Whether or not to allow POST forms to resubmit on page refresh.
  Set this to true before including utilities.php in order to override it on a per-page basis.

  DEVELOPMENT
  Whether or not the site is under development
 */

$DEVELOPER_IP_ADDRESSES = [];

// -----------------------------------------------------------------------------
// localhost
// -----------------------------------------------------------------------------
// Example: Example Website Title
const WEBSITE_TITLE = "Game Heretics";
// Example: example.com
const DOMAIN = "localhost/amp-stack-blog-template";
// Example: admin@example.com
const ADMINISTRATOR_EMAIL = "josephdboris@gmail.com";

const SENDER_EMAIL = "josephdboris@gmail.com";
// Example: "javascript/"
const JAVASCRIPT_PATH = "javascript/";
// Example: "css/"
const CSS_PATH = "css/";
// Example: "images/"
const IMAGE_PATH = "images/";

const ERROR_CODES = true;

const DATABASE_HOST = "localhost";

const DATABASE_USER = "game_heretics_admin";
// Localhost:
// phpmyadmin:
// root
// 
const DATABASE_PASSWORD = "";

const DATABASE_NAME = "template_blog_with_users";

const SUBROOT = "amp-stack-blog-template/";
// Define this as true before including utilities.php in order to override it on a per-page basis.
if( defined( "ALLOW_FORM_RESUBMISSIONS" ) == false )
{
	define( "ALLOW_FORM_RESUBMISSIONS", false );
}

const FACEBOOK_URL = "https://www.facebook.com/" . WEBSITE_TITLE;
const TWITTER_URL = "https://twitter.com/MDBootstrap/" . WEBSITE_TITLE;

define( "GOOGLE_PLUS_URL", "https://plus.google.com/+" . preg_replace( "/\s+/", "", WEBSITE_TITLE ) );

// WARNING: Do not forgot to set to false after publishing this project
const DEVELOPMENT = true;

require_once( "libraries/_blog-utilities.php" );

?>