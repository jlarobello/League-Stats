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
    $s_id2 = verify($_POST["comp"], $s_id);

    if($s_id2 == -1)
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

    $s2query1 = "select * from wins 
                 where s_id = $s_id2
                 union
                 select * from losses
                 where s_id = $s_id2
                 order by timestamp desc";
    $s2query2 = "select *
                 from stats
                 where s_id = $s_id2";
    
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
                    <h2>Comparing <b><?php echo $s1averagestats["s_name"]; ?></b> to <b><?php echo $_POST["comp"]?></b></h2>
                </div>
                <div class="col-xs-6">
                    <span class="pull-right"><a href="index.html" class="btn btn-default" role="button">Logout</a></span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <h3><?php echo $s1averagestats["s_name"];?></h3>
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
                                while($resultrow = $s1result1->fetch_assoc())
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
                    <h3><?php echo $_POST["comp"];?></h3>
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
                                while($resultrow = $s2result1->fetch_assoc())
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
            <h3>Averages</h3>
            <div class="row">
                <div class="col-xs-6">
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
                                $kills = $s1averagestats["kills"];
                                $deaths = $s1averagestats["deaths"];
                                $assists = $s1averagestats["assists"];
                                $gold = $s1averagestats["gold"];
                                $cs = $s1averagestats["cs"];
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
                <div class="col-xs-6">
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
                                $kills = $s2averagestats["kills"];
                                $deaths = $s2averagestats["deaths"];
                                $assists = $s2averagestats["assists"];
                                $gold = $s2averagestats["gold"];
                                $cs = $s2averagestats["cs"];
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
                    <a href="stats.php" class="btn btn-default" role="button">Back to stats</a>
                </div>
            </div>
        </div>
    </body>
</html>
