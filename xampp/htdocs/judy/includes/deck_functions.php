<?php
// Creates a new file name given the temp location of the original file
// Returns the new file name
function create_new_deck_filename($temp_file_location) {
	$file_contents = file_get_contents($temp_file_location);
	do {
		// Generate a 40 character hex value by hashing the value of the current time combined with the file contents
		$new_file_name = sha1(time() . $file_contents) . ".xml";
		// Repeat while the generated new file name already exists
	} while(file_exists("../../judy_uploads/decks_xml/$new_file_name"));
	return $new_file_name;
}

// Attempts to add a deck to the database
// Returns false if it fails to do so
function add_deck_to_database($dbc, $user_id, $file_name, $xml_temp_file_location) {
	// Parse the deck XML file
	$deck = simplexml_load_file($xml_temp_file_location);
	// Extract the deck name from the xml file
	$deck_name = $deck->name;
	// Create an array to hold the deck genres
	$genres = array();
	// Extract the deck genres from the xml file
	foreach ($deck->genre as $g) {
		$genres[] = $g;
	}
	
	// Add genres to database if they don't exist already
	foreach ($genres as $g) {
		$add_genre_query = "INSERT INTO genre (title) VALUES ('$g') ON DUPLICATE KEY UPDATE title = '$g'";
		@mysqli_query($dbc, $add_genre_query);
	}
	
	// Create an array to hold the corresponding genre id's for each genre
	$genre_ids = array();
	// Query database for the genre_ids
	foreach ($genres as $g) {
		$query = "SELECT genre_id FROM genre WHERE title = '$g'";
		$results = @mysqli_query($dbc, $query);
		if (!empty($results) && $results->num_rows !== 0) {
			$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
			$genre_ids[] = $row['genre_id'];
		}
	}
	
	// Query database to add deck to deck table
	$query = "INSERT INTO deck (creator_id, file_name, name, uploaded) VALUES ('$user_id', '$file_name', '$deck_name', NOW())";
	$results = @mysqli_query($dbc, $query);
	// if failure adding deck info to deck table
	if (!$results) {
		return false;
	}
	
	// Query database for deck_id
	$query = "SELECT deck_id FROM deck WHERE creator_id = '$user_id' and file_name = '$file_name' and name = '$deck_name'";
	$results = @mysqli_query($dbc, $query);
	if ($results) {
		$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
		$deck_id = $row['deck_id'];
		foreach ($genre_ids as $g_id) {
			$query = "INSERT INTO map_deck_to_genre (deck_id, genre_id) VALUES ('$deck_id', '$g_id')";
			$results = @mysqli_query($dbc, $query);
		}
	}
	
	return true;
}

// Updates the info of deck with given deck_id with the values from the given temp xml file
// Updates the following values:
//  * Update deck name
//  * Update date upload date
//  * Delete all original genre pairings
//  * Upload new genre pairings
function update_deck_in_database($dbc, $deck_id, $xml_temp_file_location) { 
	// Parse the deck XML file
	$deck = simplexml_load_file($xml_temp_file_location);
	// Extract the deck name from the xml file
	$deck_name = $deck->name;
	// Create an array to hold the deck genres
	$genres = array();
	// Extract the deck genres from the xml file
	foreach ($deck->genre as $g) {
		$genres[] = $g;
	}
	
	// Create an array to hold the corresponding genre id's for each genre
	$genre_ids = array();
	// Query database for the genre_ids
	foreach ($genres as $g) {
		$query = "SELECT genre_id FROM genre WHERE title = '$g'";
		$results = @mysqli_query($dbc, $query);
		if (!empty($results) && $results->num_rows !== 0) {
			$row = mysqli_fetch_array($results, MYSQLI_ASSOC);
			$genre_ids[] = $row['genre_id'];
		}
	}
	
	// Create query to update deck name
	$query = "UPDATE deck SET name = '$deck_name' WHERE deck_id = '$deck_id'";
	$results = @mysqli_query($dbc, $query);
	// if failure adding deck info to deck table
	if (!$results) {
		return false;
	}
	
	// Create query to update deck upload date
	$query = "UPDATE deck SET uploaded = NOW() WHERE deck_id = '$deck_id'";
	$results = @mysqli_query($dbc, $query);
	// if failure adding deck info to deck table
	if (!$results) {
		return false;
	}
	
	// Create query to delete deck genre maps
	$query = "DELETE FROM map_deck_to_genre WHERE deck_id = '$deck_id'";
	$results = @mysqli_query($dbc, $query);
	// if failure adding deck info to deck table
	if (!$results) {
		return false;
	}
	
	// Query database to add new deck genre maps
	foreach ($genre_ids as $g_id) {
		$query = "INSERT INTO map_deck_to_genre (deck_id, genre_id) VALUES ('$deck_id', '$g_id')";
		$results = @mysqli_query($dbc, $query);
	}
	
	return true;
}

// Deletes a deck from the database and server file system
function delete_deck($dbc, $deck_id, $file_name) {
	// Create query to delete deck
	$query = "DELETE FROM deck WHERE deck_id = '$deck_id' LIMIT 1";
	$results = @mysqli_query($dbc, $query);
	// if failure deleting deck
	if (!$results) {
		return false;
	}
	
	// Get filepath
	$filepath = "../../judy_uploads/decks_xml/$file_name";
	$filepath = realpath($filepath);
	if (is_writable($filepath)) {
		// Delete the file
		unlink($filepath);
	}
	
	return true;
}

// Returns an associative array containing the deck information of the passed query row
function get_deck_info($dbc, $row) {
	$deck_info = array();
	$deck_info['deck_id'] = $row['deck_id'];
	$deck_info['deck_name'] = $row['name'];
	$deck_info['upload_date'] = $row['uploaded'];
	$deck_info['file_name'] = $row['file_name'];
	$deck_xml = simplexml_load_file("../../judy_uploads/decks_xml/{$row['file_name']}");
	$deck_info['preview_card'] = $deck_xml->card[0]->content;
	$deck_info['creator_id'] = $row['creator_id'];
	$query = "SELECT username FROM deck_user WHERE user_id = '{$deck_info['creator_id']}'";
	$results = @mysqli_query($dbc, $query);
	if ($results) {
		$deck_info['username'] = mysqli_fetch_array($results, MYSQLI_ASSOC)['username'];
	}
	else {
		$deck_info['username'] = "";
	}
	
	return $deck_info;
}
?>