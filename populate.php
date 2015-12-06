<!--
    Desc:   Populate tables with user information
    Author: Jonathan Robello
    File:   populate.php
-->

<?php
    
    // Include Helper methods
    include "helpers.php";

    // Start session
    session_start();

    // For MySQL
    $servername = 'localhost';
    $username   = 'jlarobello';
    $password   = 'Ics321808';
    $dbname     = 'leaguestats';

    // Establish connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error)
    {
        $conn->close();
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "select s_id, latest_timestamp, kills, deaths, assists, gold, cs from stats
    where s_id=$_SESSION["s_id"]";

    $result = $conn->query($query);

    if($result->num_rows == 0)
    {
        
    }
?>
