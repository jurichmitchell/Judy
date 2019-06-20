<?php

require('includes/check_if_signedin.php');

// If the user is signed in
if (isset($signedin) && $signedin) {
	//Cancel the session
	$_SESSION = array(); // Clear the variables.
	session_destroy(); // Destroy the session itself.
	setcookie ('PHPSESSID', '', time()-3600, '/', '', 0, 0); // Destroy the cookie.
}
// else the user is not signed in

// In both cases, redirect the user to the index
require ('includes/login_functions.php');
redirect_user();
?>