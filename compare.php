<!-- boostrap from w3 schools-->
<?php
    include "helpers.php";

    // Start session
    session_start();
    
    // For MySQL
    $servername = 'localhost';
    $username   = 'jlarobello';
    $password   = 'Ics321808';
    $dbname     = 'leaguestats';
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set summoner s_id's
    $s_id  = $_SESSION["s_id"];
    $s_id2 = verify($_POST["comp"]);

    if($s_id1 == -1)
    {
        header("Location: invalid2.html");
        $conn->close();
        die();
    }

    // Query for stats and wins/losses
    $s1query1 = "select * from wins 
                 where s_id = $s_id
                 union
                 select * from losses
                 where s_id = $s_id
                 order by timestamp desc";
    $s1query2 = "select u.s_name, s.*
                 from users u
                 join stats s
                 on u.s_id = s.s_id
                 where u.s_id = $s_id and s.s_id = $s_id";

    $s2query2 = "select * from wins 
                 where s_id = $s_id2
                 union
                 select * from losses
                 where s_id = $s_id2
                 order by timestamp desc";
    $s2query2 = "select u.s_name, s.*
                 from users u
                 join stats s
                 on u.s_id = s.s_id
                 where u.s_id = $s_id2 and s.s_id = $s_id2";
    
    // For first user
    $s1result1      = $conn->query($s1query1);
    $s1result2      = $conn->query($s1query2);
    $s1averagestats = $s1result2->fetch_assoc();

    // For second user
    $s2result1      = $conn->query($s2query1);
    $s2result2      = $conn->query($s2query2);
    $s2averagestats = $s2result2->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>League Stats</title>
    </head>
    <body>

    </body>
</html>
