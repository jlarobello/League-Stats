<!-- boostrap from w3 schools-->
<?php
    // Start session
    session_start();
    
    // For MySQL
    $servername = 'localhost';
    $username   = 'jlarobello';
    $password   = 'Ics321808';
    $dbname     = 'leaguestats';
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set summoner s_id
    $s_id = $_SESSION["s_id"];
    
    // Query for stats and wins/losses
    $query1 = "select * from wins where s_id=$s_id order by timestamp desc";
    $query2 = "select * from losses where s_id=$s_id order by timestamp desc";
    $query3 = "select u.s_name, s.*
               from users u
               join stats s
               on u.s_id = s.s_id
               where u.s_id = $s_id and s.s_id = $s_id";
    
    $result1 = $conn->query($query1);
    $result2 = $conn->query($query2);
    $result3 = $conn->query($query3);
    
    $averagestats = $result3->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>

    </body>
</html>
