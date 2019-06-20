<?php
/****************************************************************
*	deck.php
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

// If the page was NOT loaded with a deck_id given in the get request
if (!isset($_GET['deck_id'])) {
	// Redirect user to index page
	redirect_user();
}
// Else the page was loaded with a deck_id in the get
else {
	require ("mysqli_connect.php");
	// Check if deck_id exists
	$query = "SELECT * FROM deck WHERE deck_id = '{$_GET['deck_id']}'";
	$results = mysqli_query($dbc, $query);
	if ($results && mysqli_num_rows($results) != 0) {
		$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
		// Functions needed to get deck info
		require ("includes/deck_functions.php");
		$deck_info = get_deck_info($dbc, $row);
	}
	else {
		// Redirect user to index page
		redirect_user();
	}
}

// If the update deck file form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$deck_update_response = array(); // Initialize a deck_upload_response array.
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
				// If the sql database can be updated
				if (update_deck_in_database($dbc, $deck_info['deck_id'], $_FILES['upload']['tmp_name'])) {
					// Move the file over.
					if (move_uploaded_file ($_FILES['upload']['tmp_name'], "../../judy_uploads/decks_xml/{$deck_info['file_name']}")) {
						redirect_user("deck.php?deck_id={$deck_info['deck_id']}");
					}
				}
				else {
					echo mysqli_error($dbc);
					$deck_update_response[] = '<p class="error">Deck file not uploaded. A server error occurred. Please try again later.</p>';
				}
			}
			else {
				$deck_update_response[] = '<p class="error">Deck file not uploaded. Invalid deck XML file.</p>';
			}
		}
		else {
			$deck_update_response[] = '<p class="error">Deck file not uploaded. File is not of XML type.</p>';
		}
		// Check for any errors uploading and moving the file
		if ($_FILES['upload']['error'] > 0) {
			$deck_update_response[] = '<p class="error">The deck file could not be uploaded.</p>';
			$upload_error = $_FILES['upload']['error'];
			
			if ($upload_error === 1 || $upload_error == 2) {
				$deck_update_response[] = '<p class="error">The file exceeds the maximum file upload size.</p>';
			}
			else {
				$deck_update_response[] = '<p class="error">A server error occurred. Please try again later.</p>';
			}
		}
	}
	else {
		$deck_update_response[] = '<p class="error">No deck XML file selected.</p>';
	}
	
	// Delete the file if it still exists
	if (file_exists ($_FILES['upload']['tmp_name']) && is_file($_FILES['upload']['tmp_name']) ) {
		unlink ($_FILES['upload']['tmp_name']);
	}
	
	// Check for a delete value and if it's set to true
	if (isset($_POST['delete']) && $_POST['delete'] == "true") {
		if (delete_deck($dbc, $deck_info['deck_id'], $deck_info['file_name'])) {
			// If successful redirect the user to their userpage
			$username = $_SESSION['username'];
			redirect_user("user.php?username=$username");
		}
		else {
			$deck_update_response[] = '<p class="error">Deck not deleted. A server error occurred. Please try again later.</p>';
		}
	}
}

$page_title = "{$deck_info['deck_name']}";
include ('includes/header.html');
?>

<fieldset class="center">
	<table style="width:100%;">
		<tr>
			<td style="width:100%;" valign="top">
				<p class="username"><?php echo "{$deck_info['deck_name']}"; ?></p>
				<p class="deckSubText"><a href="user.php?username=<?php echo "{$deck_info['username']}"; ?>">Created by: <?php echo "{$deck_info['username']}"; ?></a></p>
				<p class="deckSubText">Uploaded:
					<?php
					$date = date_create($deck_info['upload_date']);
					echo date_format($date,"F d, Y"); 
					?>
				</p>
			</td>
		</tr>
		<tr>
			<td style="width:100%;" valign="bottom">
				<p class="deckSubText">
				<?php
				// Generate query to get average of this deck's ratings
				$query = "SELECT AVG(rating) as avg FROM rating WHERE deck_id='{$deck_info['deck_id']}'";
				$results = mysqli_query($dbc, $query);
				if ($results && mysqli_num_rows($results) != 0) {
					echo "AVG. RATING: " . mysqli_fetch_array($results, MYSQLI_ASSOC)['avg'];
				}
				// NOT REACHED FOR SOME REASON
				else {
					echo "DECK UNRATED";
				}
				?>
				</p>
			</td>
		</tr>
	</table>
</fieldset>

<?php
// If signed in
if ($signedin) {
	// Query the database to see if the user has rated this deck
	$query = "SELECT rating FROM rating WHERE user_id = '{$_SESSION['user_id']}' and deck_id = '{$deck_info['deck_id']}'";
	$results = mysqli_query($dbc, $query);
	if ($results) {
		// Set the variable so the form can pre-check that option
		$user_rating = mysqli_fetch_array($results, MYSQLI_ASSOC)['rating'];
	}
	// Display rating form
	include ("includes/rate_deck_form.html");
	
	// If this deck belongs to the signed in user
	if ($_SESSION['username'] == $deck_info['username']) {
		// Display upload deck button form
		include ("includes/update_deck_form.html");

	}
}

// Load the deck xml file
$deck_xml = simplexml_load_file("../../judy_uploads/decks_xml/{$deck_info['file_name']}");
?>

<fieldset class="center">
	<legend>Prompts</legend>
	<div class="cardHoldingDiv">
		<?php
		foreach ($deck_xml->card as $card) {
			if ($card['type'] == "prompt") {
				echo "<div class=\"fixedRatioCardPreview\"><div class=\"cardPreviewText\">$card->content</div></div>\n\t\t";
			}
		}
		?>
	</div>
</fieldset>

<fieldset class="center">
	<legend>Responses</legend>
	<div class="cardHoldingDiv">
		<?php
		foreach ($deck_xml->card as $card) {
			if ($card['type'] == "response") {
				echo "<div class=\"fixedRatioCardPreview\"><div class=\"cardPreviewText\">$card->content</div></div>\n\t\t";
			}
		}
		?>
	</div>
</fieldset>

<?php
// Close MySQL connection
mysqli_close($dbc);
include ('includes/footer.html');
?>