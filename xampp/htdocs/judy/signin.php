<?php
/*************************************************************
*	signin.php
*	
*	Mitchell Jurich
*   Server Side Web Development - Final Project
*   Last Edit: 4/23/18
*
*	Adapted from "PHP and MySQL for Dynamic Web Sites"
*	Fourth Edition. Larry Ullman
**************************************************************/

require('includes/check_if_signedin.php');
if ($signedin) {
	// The user should not be here while signed in
	// Redirect the user to the index
	require ('includes/login_functions.php');
	redirect_user();
}
$page_title = 'Sign in';
include ('includes/header.html');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	// Need two includes
	require ('includes/login_functions.php');
	require ('mysqli_connect.php');
	
	// Check the login
	list ($successful, $errors, $data) = check_login($dbc, $_POST['email'], $_POST['password']);
	
	if ($successful) {
		
		// Set the session data
		session_start();
		$_SESSION['user_id'] = $data['user_id'];
		$_SESSION['username'] = $data['username'];
		
		// Store the HTTP_USER_AGENT
		$_SESSION['agent'] = SHA1($_SERVER['HTTP_USER_AGENT']);
		
		// Redirect to userpage
		redirect_user("user.php?username={$_SESSION['username']}");
	}
	
	// Unsuccessful
	mysqli_close($dbc);
	
	// Print any error messages, if they exist
	if (isset($errors) && !empty($errors)) {
		echo "\t\t<h1 class=\"error\">Error!</h1>\n";
		echo "\t\t<h2 class=\"error\">The following error(s) occurred:</h2>\n";
		echo "\t\t<ul>\n";
		foreach ($errors as $msg) { // Print each error
			echo "\t\t\t<li class=\"error\">$msg</li>\n";
		}
		echo "\t\t</ul>\n";
	}
	
	
}

// Display the sign in form
include ('includes/signin_form.html');

include ('includes/footer.html');
?>