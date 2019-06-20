<?php
/*************************************************************
*	register.php
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
$page_title = 'Register';
include ('includes/header.html');
include ('includes/site_info.php');

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$errors = array(); // Initialize an error array.
	
	// Make sure form is correctly filled out
	
	// Check for a username:
	if (empty($_POST['username'])) {
		$errors[] = 'You forgot to enter a username.';
	}
	else {
		$username = trim($_POST['username']);
	}
	
	// Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email address.';
	}
	else {
		$email = trim($_POST['email']);
		// Check if email is actually an email
		// Remove all invalid email characters from email address
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		// Validate e-mail
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Supplied email address is not a valid email address.';
		}
	}
	
	// Check for a password and match against the confirmed password:
	if (!empty($_POST['password'])) {
		if ($_POST['password'] != $_POST['conf_password']) {
			$errors[] = 'Your password did not match the confirmed password.';
		}
		else {
			$password = trim($_POST['password']);
		}
	}
	else {
		$errors[] = 'You forgot to enter your password.';
	}
	
	// If form was properly filled out
	if (empty($errors)) {
		// Connect to the db
		require ('mysqli_connect.php');
		
		// Make sure form values are safe
		$username = mysqli_real_escape_string($dbc, trim($_POST['username']));
		$email = mysqli_real_escape_string($dbc, trim($_POST['email']));
		$password = mysqli_real_escape_string($dbc, trim($_POST['password']));
		
		// Check if a user already exists with that username
		$query = "SELECT * from deck_user WHERE username = '$username'";
		$results = @mysqli_query($dbc, $query);
		if (!empty($results) && $results->num_rows !== 0) {
			$errors[] = 'A user with that username already exists.';
		}
		
		// Check if a user already exists with that email
		$query = "SELECT * from deck_user WHERE email = '$email'";
		$results = @mysqli_query($dbc, $query);
		if (!empty($results) && $results->num_rows !== 0) {
			$errors[] = 'A user with that email address already exists.';
		}
		
		// If the email and username are unique
		if (empty($errors)) {
			// Register the user in the database
			
			$query = "INSERT INTO deck_user(email, username, password, registration_date) VALUES ('$email', '$username', SHA1('$password'), NOW())";
			$results = @mysqli_query($dbc, $query);
			
			// If it ran OK
			if ($results) {

				// Print a message:
				echo '<h1>Thank you!</h1>
				<p>You are now registered!</p>';
			}
			// If it did not run OK
			else {
			
				// Public message:
				echo '<h1>System Error</h1>
				<p class="error">You could not be registered due to a system error. We apologize for any inconvenience.</p>'; 
				
				// Debugging message:
				if (DEBUG_ON) {
					echo '<p>' . mysqli_error($dbc) . '<br><br>Query: ' . $query . '</p>';
				}
			}
		
			mysqli_close($dbc); // Close the database connection
		}
	}
		
	// If there were errors
	if (!empty($errors)) {
		// Print the errors
		echo "\t\t<h1 class=\"error\">Error!</h1>\n";
		echo "\t\t<h2 class=\"error\">The following error(s) occurred:</h2>\n";
		echo "\t\t<ul>\n";
		foreach ($errors as $msg) { // Print each error
			echo "\t\t\t<li class=\"error\">$msg</li>\n";
		}
		echo "\t\t</ul>\n";
		echo "\t\t<h2 class=\"error\">Please try again.</h2>\n";
		
		// Display the register form
		include ('includes/register_form.html');
	}
		
} // END OF if ($_SERVER['REQUEST_METHOD'] == 'POST')
	
// If request method was not post
else {
	// Display the register form
	include ('includes/register_form.html');
}

include ('includes/footer.html');
?>