<?php
/****************************************************************
*	user.php
*	
*	Mitchell Jurich
*   Server Side Web Development - Final Project
*   Last Edit: 4/26/18
*
*	Some code adapted from "PHP and MySQL for Dynamic Web Sites"
*	Fourth Edition. Larry Ullman
*****************************************************************/

require ('includes/check_if_signedin.php');

// Functions needed to redirect
require ('includes/login_functions.php');

// If the page was loaded with a username given in the get request
if (isset($_GET['username'])) {
	// Get username sent through get request
	$username = $_GET['username'];
	// Connect to database
	require ('mysqli_connect.php');
	// Check if user exists in database
	$query = "SELECT user_id from deck_user WHERE username = '$username'";
	$results = @mysqli_query($dbc, $query);
	// If a result was returned (the user exists)
	if (!empty($results) && $results->num_rows !== 0) {
		$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
		$user_id = $row['user_id'];
	}
	else {
		// Redirect user to index page
		redirect_user();
	}
}
else {
	// If signed in
	if ($signedin) {
		// Reload page with a get request of the signed in user
		$username = $_SESSION['username'];
		redirect_user("user.php?username=$username");
	}
	else {
		// Redirect user to index page
		redirect_user();
	}
}

// If the upload deck file form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$deck_upload_response = array(); // Initialize a deck_upload_response array.
	// Check for an uploaded file
	if (isset($_FILES['upload']) && is_uploaded_file($_FILES['upload']['tmp_name'])) {
		// Validate the type. Should be xml
		$allowed = array ('text/xml');
		if (in_array($_FILES['upload']['type'], $allowed)) {
			// Make sure the xml deck file validates against the dtd file
			libxml_use_internal_errors(true); // Collect any validation errors/warnings instead of printing them to screen
			$dom = new DOMDocument();
			// Load the temp XML file
			$dom->load("{$_FILES['upload']['tmp_name']}");
			// Validate the loaded XML file against the XML Scheme Definition (XSD)
			if ($dom->schemaValidate("xml/deck.xsd")){
				// The file has been validated and is properly formatted to be a deck xml file
				// Functions needed for deck operations
				require ("includes/deck_functions.php");
				// Create a new unique filename
				$new_file_name = create_new_deck_filename($_FILES['upload']['tmp_name']);
				// Try adding deck to database
				// If successfully added to database
				if (add_deck_to_database($dbc, $user_id, $new_file_name, $_FILES['upload']['tmp_name'])) {
					// Move the file over.
					if (move_uploaded_file ($_FILES['upload']['tmp_name'], "../../judy_uploads/decks_xml/$new_file_name")) {
						$deck_upload_response[] = "<p>Deck file successfully uploaded!</p>";
					}
				}
				else {
					$deck_upload_response[] = '<p class="error">Deck file not uploaded. A server error occurred. Please try again later.</p>';
				}
			}
			else {
				$deck_upload_response[] = '<p class="error">Deck file not uploaded. Invalid deck XML file.</p>';
			}
		}
		else {
			$deck_upload_response[] = '<p class="error">Deck file not uploaded. File is not of XML type.</p>';
		}
		// Check for any errors uploading and moving the file
		if ($_FILES['upload']['error'] > 0) {
			$deck_upload_response[] = '<p class="error">The deck file could not be uploaded.</p>';
			$upload_error = $_FILES['upload']['error'];
			
			if ($upload_error === 1 || $upload_error == 2) {
				$deck_upload_response[] = '<p class="error">The file exceeds the maximum file upload size.</p>';
			}
			else {
				$deck_upload_response[] = '<p class="error">A server error occurred. Please try again later.</p>';
			}
		}
	}
	else {
		$deck_upload_response[] = '<p class="error">No deck XML file selected.</p>';
	}
	
	// Delete the file if it still exists
	if (file_exists ($_FILES['upload']['tmp_name']) && is_file($_FILES['upload']['tmp_name']) ) {
		unlink ($_FILES['upload']['tmp_name']);
	}
}

$page_title = "$username";
include ('includes/header.html');
?>

<fieldset>
	<table>
		<tr>
			<td rowspan="2">
				<img class="profile_pic" src="<?php
				$query = "SELECT profile_pic FROM deck_user WHERE user_id = '$user_id'";
				$results = @mysqli_query($dbc, $query);
				$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
				if ($row['profile_pic'] != "") {
					echo "show_image.php?sub_dir=profile_pics/&image={$row['profile_pic']}";
				}
				else {
					echo "images/default_profile_pic.png";
				}
				?>"/>
			</td>
			<td style="width:100%;" valign="top"><p class="username"><?php echo "$username"; ?></p>
				<p class="reg_date">User since: 
					<?php // Get user registration date
					$query = "SELECT registration_date from deck_user WHERE user_id = '$user_id'";
					$results = @mysqli_query($dbc, $query);
					$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
					$date = date_create($row['registration_date']);
					echo date_format($date,"F d, Y");
					?>
				</p>
			</td>
		</tr>
		<tr>
			<?php // If signed in and this userpage belongs to the signed in user
			if ($signedin && $_SESSION['username'] == $username) {
				// Display user settings
				echo '<td style="width:100%;" valign="bottom"><form enctype="multipart/form-data" action="update_profile_pic.php" method="post">';
				echo '<input type="hidden" name="user_id" value="' . $_SESSION['user_id'] . '"/>';
				echo '<input class="inputfile" type="file" name="upload_profile_pic" id="upload_profile_pic"/>';
				echo '<label class="button" for="upload_profile_pic" id="inputProfilePicLabel"><img class="uploadImage" src="images/upload.png"/><span>Pick Profile Picture</span></label>';
				echo '<input class="button" id="uploadFileButton" type="submit" name="update_submit" value="Upload" />';
				echo '</form></td>';
			}
			?>
		</tr>
	</table>
</fieldset>

<?php
// If signed in and this userpage belongs to the signed in user
if ($signedin && $_SESSION['username'] == $username) {
	// Display upload deck button form
	include ("includes/upload_deck_form.html");
}

// See if the user has uploaded any decks
$query = "SELECT * FROM deck WHERE creator_id = '$user_id' ORDER BY name";
$results = @mysqli_query($dbc, $query);
if ($results) {
	echo "<fieldset class=\"center\"><legend>Decks</legend>\n";
	// Include the deck_functions.php file if we haven't already
	include_once ("includes/deck_functions.php");
	while ($row = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
		$deck_info = get_deck_info($dbc, $row);
		include ("includes/deck_preview.php");
	}
	echo "</fieldset>";
}
?>


<?php
// Close MySQL connection
mysqli_close($dbc);
include ('includes/footer.html');
?>