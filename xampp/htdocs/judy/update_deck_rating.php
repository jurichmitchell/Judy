<?php
require ("includes/check_if_signedin.php");
require ("includes/login_functions.php");
// If this page was called via a get request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	// If the 3 required values are set
	if (isset($_GET['rating']) && isset($_GET['user_id']) && isset($_GET['deck_id'])) {
		// If the current signed in user is the same user sent via the get request
		if ($signedin && $_SESSION['user_id'] == $_GET['user_id']) {
			require ("mysqli_connect.php");
			// Generate a query to add the user's rating to the database
			$query = "INSERT INTO rating (user_id, deck_id, rating) VALUES ({$_GET['user_id']}, {$_GET['deck_id']}, {$_GET['rating']}) ON DUPLICATE KEY UPDATE rating = '{$_GET['rating']}'";
			$result = mysqli_query($dbc, $query);	
			// Redirect user to deck page
			redirect_user("deck.php?deck_id={$_GET['deck_id']}");
		}
	}
}
// If any of the if cases fail redirect user to index page
redirect_user();
?>