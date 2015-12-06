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

    // Summoner id
    $s_id = $_SESSION["s_id"];

    // Establish connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error)
    {
        $conn->close();
        die("Connection failed: " . $conn->connect_error);
    }

    $query  = "select * from stats where s_id=$s_id";
    $result = $conn->query($query);
    
    if($result->num_rows == 0)
    {
        $json = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/matchlist/by-summoner/$s_id?rankedQueues=RANKED_SOLO_5x5&seasons=SEASON2015&api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
        $obj  = json_decode($json, TRUE);
        
        for($i = 0; $i < 5; $i++) // max 5 because of API request cap
        {
            $matchid    = $obj["matches"][$i]["matchId"];
            $championid = $obj["matches"][$i]["champion"];
            $timestamp  = $obj["matches"][$i]["timestamp"];
            populate($conn, $matchid, $timestamp, $championid);
        }
    }
?>
