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

    if($conn->connect_error)
    {
        $conn->close();
        die("Connection failed: " . $conn->connect_error);
    }

    $query  = "select * from stats where s_id=$s_id";
    $result = $conn->query($query);
    
    if($result->num_rows == 0)
    {
        $json = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/matchlist/by-summoner/$s_id?seasons=PRESEASON2016&api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
        $obj  = json_decode($json, TRUE);

        if($obj["totalGames"] == 0)
        {
            header("Location: stats.php");
            $conn->close();
            die();
        }
        
        for($i = 0; $i < 10; $i++) // max 5 because of API request cap
        {
            $matchid    = $obj["matches"][$i]["matchId"];
            $championid = $obj["matches"][$i]["champion"];
            $timestamp  = $obj["matches"][$i]["timestamp"];
            populate($matchid, $timestamp, $championid, $s_id); // 1 API request per a call.
            sleep(1.5);
        }

        $query   = "select * from wins
                    where wins.s_id = $s_id
                    union
                    select * from losses
                    where losses.s_id = $s_id
                    order by timestamp desc";
        $results = $conn->query($query);
        $numrows = $results->num_rows;

        $latest_timestamp = 0;
        $kills            = 0;
        $deaths           = 0;
        $assists          = 0;
        $gold             = 0;
        $cs               = 0;

        $resultrow = $results->fetch_assoc();

        $latest_timestamp = $resultrow["timestamp"];
        $kills            = $resultrow["kills"];
        $deaths           = $resultrow["deaths"];
        $assists          = $resultrow["assists"];
        $gold             = $resultrow["gold"];
        $cs               = $resultrow["cs"];

        while(($resultrow = $results->fetch_assoc()))
        {
            $kills   += $resultrow["kills"];
            $deaths  += $resultrow["deaths"];
            $assists += $resultrow["assists"];
            $gold    += $resultrow["gold"];
            $cs      += $resultrow["cs"];
        }
        
        $kills   /= $numrows;
        $deaths  /= $numrows;
        $assists /= $numrows;
        $gold    /= $numrows;
        $cs      /= $numrows;

         // Average stats
         $query  = "insert into stats(s_id, latest_timestamp, kills, deaths, assists, gold, cs) 
                    values($s_id, $latest_timestamp, $kills, $deaths, $assists, $gold, $cs)";
         $result = $conn->query($query);
         $conn->close();
         header("Location: stats.php");
    }
    else
    {
        $json       = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/matchlist/by-summoner/$s_id?seasons=PRESEASON2016&api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
        $obj        = json_decode($json, TRUE);
        $timestamp  = $obj["matches"][0]["timestamp"];

        $query     = "select latest_timestamp from stats
                      where s_id = $s_id";
        $result    = $conn->query($query);
        $resultrow = $result->fetch_assoc();

        if($result->numw_rows > 0 && $timestamp > $resultrow["latest_timestamp"] && !empty($obj))
        {
            repopulate($obj, $s_id);
        }
        $conn->close();
        header("Location: stats.php");
    }
?>
