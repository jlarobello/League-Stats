<?php
    
   
    // Name: populate
    // Desc: populates wins/losses table with wins/losses
    // Parameters: matchid   - the match id
    //             timestamp - time of match
    //             champid   - the user in the match
    // Return: void
    function populate($matchid, $timestamp, $champid)
    {

         // For MySQL
         $servername = 'localhost';
         $username   = 'jlarobello';
         $password   = 'Ics321808';
         $dbname     = 'leaguestats';

         // Establish connection
         $conn = new mysqli($servername, $username, $password, $dbname);

         $s_id = $_SESSION["s_id"];
         $json = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/match/$matchid?api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
         $obj  = json_decode($json, TRUE);

         $champIndex = 0;
         
         for($i = 0; $i < 10; $i++)
         { 
             $temp = $obj["participants"][$i]["championId"];
             if(strcmp($temp, $champid) == 0)
             {
                 $champIndex = $i;
             }
         }

         $kills     = $obj["participants"][$champIndex]["stats"]["kills"];
         $deaths    = $obj["participants"][$champIndex]["stats"]["deaths"];
         $assists   = $obj["participants"][$champIndex]["stats"]["assists"];
         $totalgold = $obj["participants"][$champIndex]["stats"]["goldEarned"];
         $winner    = $obj["participants"][$champIndex]["stats"]["winner"];
         $totalcs   = $obj["participants"][$champIndex]["stats"]["minionsKilled"] 
                      + $obj["participants"][$champIndex]["stats"]["neutralMinionsKilled"];

         $table  = ($winner == 1) ? "wins": "losses";
         $query  = "insert into $table(s_id, timestamp, matchid, kills, deaths, assists, gold, cs) 
                   values($s_id, $timestamp, $matchid, $kills, $deaths, $assists, $totalgold, $totalcs)";
         $result = $conn->query($query);
         $conn->close();
    }

    // Name: update
    // Desc: updates a summoners win/loss and stats
    // Parameters: matchid - the match
    //             timestamp - time of match
    //             champid - the user in the match
    function update($matchid, $timestamp, $champid)
    {
        // For MySQL
         $servername = 'localhost';
         $username   = 'jlarobello';
         $password   = 'Ics321808';
         $dbname     = 'leaguestats';

         // Establish connection
         $conn = new mysqli($servername, $username, $password, $dbname);

         $s_id = $_SESSION["s_id"];
         $json = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/match/$matchid?api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
         $obj  = json_decode($json, TRUE);

         $champIndex = 0;
         
         for($i = 0; $i < 10; $i++)
         { 
             $temp = $obj["participants"][$i]["championId"];
             if(strcmp($temp, $champid) == 0)
             {
                 $champIndex = $i;
             }
         }

         $kills     = $obj["participants"][$champIndex]["stats"]["kills"];
         $deaths    = $obj["participants"][$champIndex]["stats"]["deaths"];
         $assists   = $obj["participants"][$champIndex]["stats"]["assists"];
         $totalgold = $obj["participants"][$champIndex]["stats"]["goldEarned"];
         $winner    = $obj["participants"][$champIndex]["stats"]["winner"];
         $totalcs   = $obj["participants"][$champIndex]["stats"]["minionsKilled"] 
                      + $obj["participants"][$champIndex]["stats"]["neutralMinionsKilled"];

         $table  = ($winner == 1) ? "wins": "losses";
         $query  = "insert into $table(s_id, timestamp, matchid, kills, deaths, assists, gold, cs) 
                   values($s_id, $timestamp, $matchid, $kills, $deaths, $assists, $totalgold, $totalcs)";
         $result = $conn->query($query);

         // Delete game with min(timestamp)
         $query     = "select * from wins
                       where wins.s_id = $s_id
                       union
                       select * from losses
                       where losses.s_id = $s_id
                       order by timestamp asc";
         $result    = $conn->query($query);
         $resultrow = $result->fetch_assoc();

         $timestamp = $resultrow["timestamp"];
         $matchid   = $resultrow["matchid"];

         $query  = "delete from wins where s_id=$s_id and timestamp = $timestamp and matchid = $matchid";
         $result = $conn->query($query);
         $query  = "delete from losses where s_id=$s_id and timestamp = $timestamp and matchid = $matchid";
         $result = $conn->query($query);

         // Reupdate summoners stats
         $query   = "select * from wins
                     where wins.s_id = $s_id
                     union
                     select * from losses
                     where losses.s_id = $s_id
                     order by timestamp desc";
         $results = $conn->query($query);
         $numrows = $results->num_rows;

         $latest_timestamp = $kills = $deaths = $assists = $gold = $cs = 0;

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
    }
?>

