<!-- bootstrap from W3 schools -->

<!DOCTYPE html>
<html lang="en">

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

<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>

<body>

<div class="container">
  <h2>Stats for <?php echo $averagestats["s_name"]; ?></h2>
  <div class="row">
  <div class="col-xs-6">   
    <h3>Wins</h3>       
    <table class="table">
     <thead>
        <tr>
          <th>Date</th>
          <th>Kills</th>
          <th>Deaths</th>
          <th>Assists</th>
          <th>Total Gold</th>
          <th>CS</th>
        </tr>
       </thead>
     <tbody>
        <?php
            while($resultrow = $result1->fetch_assoc())
            {
                $date = date("m/d/Y", ($resultrow["timestamp"]/1000));
                $kills = $resultrow["kills"];
                $deaths = $resultrow["deaths"];
                $assists = $resultrow["assists"];
                $gold = $resultrow["gold"];
                $cs = $resultrow["cs"];
                echo "<tr>
                        <td>$date</td>
                        <td>$kills</td>
                        <td>$deaths</td>
                        <td>$assists</td>
                        <td>$gold</td>
                        <td>$cs</td>
                      </tr>";
            }
        ?>
     </tbody>
    </table>
    </div>
      <div class="col-xs-6"> 
       <h3>Losses</h3>          
    <table class="table">
     <thead>
        <tr>
          <th>Date</th>
          <th>Kills</th>
          <th>Deaths</th>
          <th>Assists</th>
          <th>Total Gold</th>
          <th>CS</th>
        </tr>
       </thead>
     <tbody>
        <?php
            while($resultrow = $result2->fetch_assoc())
            {
                $date = date("m/d/Y", ($resultrow["timestamp"]/1000));
                $kills = $resultrow["kills"];
                $deaths = $resultrow["deaths"];
                $assists = $resultrow["assists"];
                $gold = $resultrow["gold"];
                $cs = $resultrow["cs"];
                echo "<tr>
                        <td>$date</td>
                        <td>$kills</td>
                        <td>$deaths</td>
                        <td>$assists</td>
                        <td>$gold</td>
                        <td>$cs</td>
                      </tr>";
            }
        ?>
        </tbody>
    </table>
    </div>
  </div>
</div>

</body>
</html>
