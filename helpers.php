<!-- Helper methods -->

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

    // Name: repopulate
    // Desc: updates a summoners win/loss and stats
    // Parameters: obj - the json matchlist object
    function repopulate($obj)
    {
         // For MySQL
         $servername = 'localhost';
         $username   = 'jlarobello';
         $password   = 'Ics321808';
         $dbname     = 'leaguestats';

         // Establish connection
         $conn = new mysqli($servername, $username, $password, $dbname);

         $s_id = $_SESSION["s_id"];

         // Delete current rows in wins/losses tables
         $query  = "delete w.*, l.* 
                    from wins w
                    left join losses l 
                    on w.s_id = l.s_id
                    where w.s_id = $s_id and l.s_id = $s_id;";
         $result = $conn->query($query);

         // Delete current summoners stats
         $query  = "delete from stats where s_id = $s_id";
         $result = $conn->query($query);

         for($i = 0; $i < 5; $i++) // max 5 because of API request cap
         {
             $matchid    = $obj["matches"][$i]["matchId"];
             $championid = $obj["matches"][$i]["champion"];
             $timestamp  = $obj["matches"][$i]["timestamp"];
             populate($matchid, $timestamp, $championid); // 1 API request per a call.
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
    }
?>

