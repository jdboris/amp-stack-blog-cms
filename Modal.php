<?php

// TODO: Define all modal messages here. *IMPORTANT* to prevent XSS

namespace Modal;

include_once( "utilities.php" );

const GENERAL_ERROR = "<strong>Error " . \ERRORS\GENERAL . ":</strong> " . \ERRORS\ERRORS[ \ERRORS\GENERAL ];
const SIGNUP_SUCCESS = "<strong>Success:</strong> Welcome to " . WEBSITE_TITLE . "! Please check your email to verify your account, and take advantage of the many benefits of being a verified user.";
const EMAIL_VERIFICATION = "Email verification status pending. A verification email was sent to your new address.";
const PASSWORD_CHANGE_EMAIL = "Password change request received. Check your email for instructions to change your password.";
const POST_CREATE_SUCCESS = "Success: new post submitted.";
const POST_EDIT_SUCCESS = "Success: post edit successful.";
const POST_DELETE_SUCCESS = "Post deleted successfully.";

