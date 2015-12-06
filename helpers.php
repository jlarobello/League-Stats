<?php
    // Name: populate
    // Desc: populates wins/losses table with wins/losses
    // Parameters: matchid   - the match id
    //             timestamp - time of match
    //             champid   - the user in the match
    //             s_id      - summonner id
    // Return: void
    function populate($matchid, $timestamp, $champid, $s_id)
    {
         $json = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/match/$matchid?api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
         $obj  = json_decode($json, TRUE);

         $champIndex = 0;
         
         for($i = 0; $i < 5; $i++)
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
         $totalcs   = $obj["participants"][$champIndex]["stats"]["minionsKilled"] 
                      + $obj["participants"][$champIndex]["stats"]["neutralMinionsKilled"];
         $winner    = $obj["participants"][$champIndex]["stats"]["winner"];

         $table = ($winner == 1) ? "wins": "losses";

         $query = "insert into $table(s_id, timestamp, matchid, kills, deaths, assists, gold, cs) 
            values($s_id, $timestamp, $matchid, $kills, $deaths, $assists, $totalgold, $totalcs)";

         if($conn->query($query) == TRUE)
         {
                echo "New record created";
                header("Location: stats.html");
                $conn->close();
                die();
         }
    }
?>

