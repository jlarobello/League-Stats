<!--
    Desc:   Validation for form
    Author: Jonathan Robello
    File:   validate.php
-->

<?php

    // Start session
    session_start();
    
    // For MySQL
    $servername = 'localhost';
    $username   = 'jlarobello';
    $password   = 'Ics321808';
    $dbname     = 'leaguestats';

    // username, pass, and url for api
    $s_name = $_POST["usr"];
    $s_pass = $_POST["pwd"];
    $url    = 'https://na.api.pvp.net/api/lol/na/v1.4/summoner/by-name/'.rawurlencode($s_name).
        '?api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e';

    if(empty($_POST["usr"]) || empty($_POST["pwd"]))
    {
        $conn->close();
        header("Location: invalid.html");
        die();
    }

    // Establish connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error)
    {
        $conn->close();
        die("Connection failed: " . $conn->connect_error);
    }

    // Determine which button was clicked and check user credentials.
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {

        $json = file_get_contents($url);
        $obj  = json_decode($json, true);
       
        if(empty($obj))
        {
            header("Location: invalid.html");
            $conn->close();
            die();
        }

        $temp  = preg_replace('/\s/', '', $s_name);
        $temp  = strtolower($temp);
        $s_id  = $obj[$temp]['id'];
        
        // Set s_id for session
        $_SESSION["s_id"] = $s_id;

        if(isset($_POST['create']))
        {
            $query  = "select s_id from users where s_id=$s_id";
            $result = $conn->query($query);
          
            if($result->num_rows > 0)
            {
                echo "User already exist!";
                $conn->close();
                header("Location: invalid.html");
                die();
            }

            $query = "insert into users(s_name, s_id, s_pass) 
            values('$s_name', $s_id, '$s_pass')";
            
            if($conn->query($query) == TRUE)
            {
                echo "New record created";
                header("Location: populate.php");
                $conn->close();
                die();
            }
            else
            {
                echo "Error: " . "<br>" . $conn->error;
            }
        }
        else if(isset($_POST['validate']))
        {
            $query  = "select s_id from users where s_id=$s_id and s_pass='$s_pass'";
            $result = $conn->query($query);

            if($result->num_rows > 0)
            {
                $conn->close();
                header("Location: populate.php");
            }
            else
            {
                $conn->close();
                header("Location: invalid.html");
            }
            die();
        }
    }
    $conn->close();
?>
