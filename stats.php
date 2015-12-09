<!-- bootstrap from W3 schools -->
<?php
    // Start session
    session_start();
    
    // For MySQL
    $servername = 'localhost';
    $username   = 'jlarobello';
    $password   = 'Ics321808';
    $dbname     = 'leaguestats';
    
    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error)
     {
         $conn->close();
         die("Connection failed: " . $conn->connect_error);
     }
    
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
        <title>League Stats</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    </head>

    <body>

        <div class="container">
            <div class="row">
                <div class="col-xs-6">
                    <h2>Stats for <b><?php $name = ($result3->num_rows > 0) ? $averagestats["s_name"]: $_SESSION["usr"]; echo $name;?></b></h2>
                </div>
                <div class="col-xs-6">
                    <span class="pull-right"><a href="index.html" class="btn btn-default" role="button">Logout</a></span>
                </div>
            </div>
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
            <div class="row">
                <div class="col-xs-6">
                    <h3>Averages</h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Kills</th>
                            <th>Deaths</th>
                            <th>Assists</th>
                            <th>Gold</th>
                            <th>CS</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                                $kills = $averagestats["kills"];
                                $deaths = $averagestats["deaths"];
                                $assists = $averagestats["assists"];
                                $gold = $averagestats["gold"];
                                $cs = $averagestats["cs"];
                                echo "<tr>
                                            <td>$kills</td>
                                            <td>$deaths</td>
                                            <td>$assists</td>
                                            <td>$gold</td>
                                            <td>$cs</td>
                                      </tr>";
                               $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <form role="form" action="compare.php" method="post">
                        <h3>Compare Stats</h3>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-default" name="compare">Compare</button>
                            </span>
                            <input type="text" class="form-control" name="comp" id="comp" placeholder="Enter summoner name to compare">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
