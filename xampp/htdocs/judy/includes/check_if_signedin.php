<?php
/*************************************************************
*	check_if_signedin.php
*	
*	Mitchell Jurich
*   Server Side Web Development - Final Project
*   Last Edit: 4/23/18
*
*	Adapted from "PHP and MySQL for Dynamic Web Sites"
*	Fourth Edition. Larry Ullman
**************************************************************/

session_start(); // Start the session.

// Validate the HTTP_USER_AGENT and other session variables
// If any aren't set then the user is not signed in
if ( !isset($_SESSION['agent']) // If the agent info isn't set
	|| ($_SESSION['agent'] != SHA1($_SERVER['HTTP_USER_AGENT'])) // If the agent info doesn't match the current agent info from the machine
	|| !isset($_SESSION['user_id']) // If the user_id isn't set
	|| !isset($_SESSION['username']) ) { // If the username isn't set
	$signedin = false;
}
else {
	$signedin = true;
}