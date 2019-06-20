<?php
require ("includes/check_if_signedin.php");
require ("includes/login_functions.php");

// Creates a new file name given the temp location of the original file
// Returns the new file name
function create_new_filename($temp_file_location, $file_extension) {
	$file_contents = file_get_contents($temp_file_location);
	do {
		// Generate a 40 character hex value by hashing the value of the current time combined with the file contents
		$new_file_name = sha1(time() . $file_contents) . "." . $file_extension;
		// Repeat while the generated new file name already exists
	} while(file_exists("../../judy_uploads/images/profile_pics/$new_file_name"));
	return $new_file_name;
}

// If the upload deck file form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$deck_upload_response = array(); // Initialize a deck_upload_response array.
	// Check for an uploaded file and make sure the user_id value has been supplied
	if (isset($_POST['user_id']) && isset($_FILES['upload_profile_pic']) && is_uploaded_file($_FILES['upload_profile_pic']['tmp_name'])) {
		// If the current signed in user is the same user sent via the post request
		if ($signedin && $_SESSION['user_id'] == $_POST['user_id']) {
			// Validate the type. Should be an image
			$allowed = array ('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png');
			if (in_array($_FILES['upload_profile_pic']['type'], $allowed)) {
				// Connect to database
				require ("mysqli_connect.php");
				
				// Delete original profile pic
				$query = "SELECT profile_pic FROM deck_user WHERE user_id = '{$_POST['user_id']}'";
				$results = @mysqli_query($dbc, $query);
				$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
				if ($row['profile_pic'] != "") {
					// Get filepath
					$filepath = "../../judy_uploads/images/profile_pics/{$row['profile_pic']}";
					$filepath = realpath($filepath);
					if (is_writable($filepath)) {
						// Delete the file
						unlink($filepath);
					}
				}
				
				// Create a new unique filename
				$new_file_name = create_new_filename($_FILES['upload_profile_pic']['tmp_name'], pathinfo($_FILES['upload_profile_pic']['name'], PATHINFO_EXTENSION));
				// Create query to add filename to database
				$query = "UPDATE deck_user SET profile_pic = '$new_file_name' WHERE user_id = '{$_POST['user_id']}'";
				$result = mysqli_query($dbc, $query);
				// Move the file into the permanent folder
				move_uploaded_file ($_FILES['upload_profile_pic']['tmp_name'], "../../judy_uploads/images/profile_pics/$new_file_name");
				// Redirect user to their userpage
				redirect_user("user.php?username={$_SESSION['username']}");
			}
		}
	}
}

// If any of the if cases fail redirect user to index page
redirect_user();
?>