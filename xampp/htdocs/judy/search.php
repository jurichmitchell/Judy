<!------------------------------------------------------------
-	search.php
-	
-	Mitchell Jurich
-   Server Side Web Development - Final Project
-   Last Edit: 4/26/18
-------------------------------------------------------------->

<?php
require('includes/check_if_signedin.php');
$page_title = 'Judy Search';
include ('includes/header.html');
?>

		<form>
			<fieldset>
				<legend>Search</legend>
				<p><label>Deck Name: <input type="text" name="deck_name" size="30" maxlength="50" /></label></p>
				<p><label>Creator: <input type="text" name="creator" size="30" maxlength="30" /></label></p>
				<p><label>Genre:
					<select name="genre">
						<option value="null"></option>
						<?php
						// Connect to database
						require ("mysqli_connect.php");
						// Get all genres and genre ids from database
						$get_genre_id_query = "SELECT * from genre";
						$genre_id_result = mysqli_query($dbc, $get_genre_id_query);
						// Create options
						if ($genre_id_result) {
							while ($row = mysqli_fetch_array($genre_id_result, MYSQLI_ASSOC)) {
								echo "<option value=\"{$row['genre_id']}\">{$row['title']}</option>\n\t\t\t\t\t\t";
							}
						}
						?>
					</select>
				</label></p>
				<p><label for="rating">Average User Rating: </label><br>
					<label><input type = "radio" name="rating" value="4+"/>&#9733 &#9733 &#9733 &#9733 &#9734 and up</label><br>
					<label><input type = "radio" name="rating" value="3+"/>&#9733 &#9733 &#9733 &#9734 &#9734 and up</label><br>
					<label><input type = "radio" name="rating" value="2+"/>&#9733 &#9733 &#9734 &#9734 &#9734 and up</label><br>
					<label><input type = "radio" name="rating" value="1+"/>&#9733 &#9734 &#9734 &#9734 &#9734 and up</label><br>
					<label><input type = "radio" name="rating" checked="true" value="0+"/>&#9734 &#9734 &#9734 &#9734 &#9734 and up</label><br>
				</p>
				<p><input type="submit" name="submit" value="Search" /></p>
			</fieldset>
		</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['submit'])) {
		$previous_qualifier = false; // Flag to track if a qualifier has been placed before the current one (used to place "and" correctly)
		// Build query based on form data
		$search_query = "SELECT d.deck_id, d.creator_id, d.file_name, d.name, d.uploaded, u.username as creator, AVG(r.rating) as avg_rating";
		$search_query = $search_query . " FROM deck as d";
		$search_query = $search_query . " INNER JOIN deck_user as u ON d.creator_id = u.user_id";
		$search_query = $search_query . " LEFT JOIN rating as r ON d.deck_id = r.deck_id";
		$search_query = $search_query . " JOIN map_deck_to_genre as genre_map ON d.deck_id = genre_map.deck_id";
		if (isset($_GET['deck_name']) && !empty($_GET['deck_name'])) {
			if ($previous_qualifier) {
				$search_query = $search_query . " AND name = '{$_GET['deck_name']}'";
			}
			else {
				$search_query = $search_query . " WHERE name = '{$_GET['deck_name']}'";
				$previous_qualifier = true;
			}
		}
		if (isset($_GET['creator']) && !empty($_GET['creator'])) {
			if ($previous_qualifier) {
				$search_query = $search_query . " AND u.username = '{$_GET['creator']}'";
			}
			else {
				$search_query = $search_query . " WHERE u.username = '{$_GET['creator']}'";
				$previous_qualifier = true;
			}
		}
		if (isset($_GET['genre'])  && $_GET['genre'] != "null") {
			//$get_genre_id_query = "SELECT genre_id from genre WHERE title = "
			if ($previous_qualifier) {
				$search_query = $search_query . " AND genre_map.genre_id = '{$_GET['genre']}'";
			}
			else {
				$search_query = $search_query . " WHERE genre_map.genre_id = '{$_GET['genre']}'";
				$previous_qualifier = true;
			}
		}
		// Add GROUP BY clause
		$search_query = $search_query . " GROUP BY d.deck_id";
		if (isset($_GET['rating']) && $_GET['rating'] != "0+") {
			if ($previous_qualifier) {
				$search_query = $search_query . " HAVING ";
			}
			else {
				$search_query = $search_query . " HAVING ";
				$previous_qualifier = true;
			}
			switch($_GET['rating']) {
				case "4+": $search_query = $search_query . " AVG(r.rating) >= 4";
					break;
				case "3+": $search_query = $search_query . " AVG(r.rating) >= 3";
					break;
				case "2+": $search_query = $search_query . " AVG(r.rating) >= 2";
					break;
				case "1+": $search_query = $search_query . " AVG(r.rating) >= 1";
					break;
			}
		}
		// Add ORDER BY clause
		$search_query = $search_query . " ORDER BY d.name";
		// Run query
		$results = mysqli_query($dbc, $search_query);
		if ($results) {
			echo "<fieldset class=\"center\"><legend>Results</legend>\n";
			// Include the deck_functions.php file if we haven't already
			include_once ("includes/deck_functions.php");
			while ($row = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
				$deck_info = get_deck_info($dbc, $row);
				include ("includes/deck_preview.php");
			}
			echo "</fieldset>";
		}
		if (defined('DEBUG_ON') && DEBUG_ON) {
			echo "<fieldset><legend>Generated Query</legend><h2>$search_query</h2></fieldset>";
			//echo mysqli_error($dbc);
		}
	}
}

include ('includes/footer.html');
?>