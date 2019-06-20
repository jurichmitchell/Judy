<?php
/*************************************************************
*	login_functions.php
*	
*	Mitchell Jurich
*   Server Side Web Development - Final Project
*   Last Edit: 4/23/18
*
*	Adapted from "PHP and MySQL for Dynamic Web Sites"
*	Fourth Edition. Larry Ullman
**************************************************************/

/* This function determines an absolute URL and redirects the user there.
 * The function takes one argument: the page to be redirected to.
 * The argument defaults to index.php.
 */
function redirect_user ($page = 'index.php') {
	
	// Start defining the URL
	// URL is http:// plus the host name plus the current directory
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	
	// Remove any trailing slashes
	$url = rtrim($url, '/\\');
	
	// Add the page
	$url .= '/' . $page;
	
	// Redirect the user
	echo "$url";
	header("Location: $url");
	exit(); // Quit the script

} // End of redirect_user() function

/* This function validates the form data (the email address and password).
 * If both are present, the database is queried.
 * The function requires a database connection.
 * The function returns
 * - a TRUE/FALSE variable indicating success
 * - an array of errors if success is FALSE, otherwise null
 * - an array of the database result if success is TRUE, otherwise null
 */
function check_login($dbc, $given_email = '', $given_password = '') {
	
	$errors = array(); // Initialize error array
	
	// Validate the email address
	if (empty($given_email)) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		$email = mysqli_real_escape_string($dbc, trim($given_email));
	}
	
	// Validate the password
	if (empty($given_password)) {
		$errors[] = 'You forgot to enter your password.';
	} else {
		$password = mysqli_real_escape_string($dbc, trim($given_password));
	}
	
	// If everything is OK
	if (empty($errors)) {
		
		// Retrieve the user_id and username for that email/password combination
		$query = "SELECT user_id, username FROM deck_user WHERE email='$email' AND password=SHA1('$password')";		
		$result = @mysqli_query ($dbc, $query); // Run the query
		
		// Check the result
		if (mysqli_num_rows($result) == 1) {
			
			// Fetch the record
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
			// Return true and the record
			return array(true, null, $row);
			
		} else { // Not a match!
			$errors[] = 'The email address and password entered do not match those on file.';
		}
		
	} // End of empty($errors) IF
	
	// Return false and the errors
	return array(false, $errors, null);

} // End of check_login() function
