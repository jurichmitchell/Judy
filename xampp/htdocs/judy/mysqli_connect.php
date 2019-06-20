<?php
/*************************************************************
*	mysqli_connect.php
*	
*	Mitchell Jurich
*   Server Side Web Development - Final Project
*   Last Edit: 4/23/18
*
*	Adapted from "PHP and MySQL for Dynamic Web Sites"
*	Fourth Edition. Larry Ullman
*
*	This file contains the database access information.
*	This file also establishes a connection to MySQL,
*	selects the database, and sets the encoding.
**************************************************************/

// Set the database information access information as constants
if (!defined('DB_USER'))
	DEFINE ('DB_USER', 'root');
if (!defined('DB_PASSWORD'))
	DEFINE ('DB_PASSWORD', '');
if (!defined('DB_HOST'))
	DEFINE ('DB_HOST', 'localhost');
if (!defined('DB_NAME'))
	DEFINE ('DB_NAME', 'deck_db');

// Make the connection
$dbc = @mysqli_connect (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error() );

// Set the encoding
mysqli_set_charset($dbc, 'utf8');