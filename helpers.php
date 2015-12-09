<!-- Helper methods -->
<?php
    // Name: populate
    // Desc: populates wins/losses table with wins/losses
    // Parameters: matchid   - the match id
    //             timestamp - time of match
    //             champid   - the user in the match
    //             s_id1     - summoner id 
    // Return: void
    function populate($matchid, $timestamp, $champid, $s_id1)
    {
    
         // For MySQL
         $servername = 'localhost';
         $username   = 'jlarobello';
         $password   = 'Ics321808';
         $dbname     = 'leaguestats';
    
         // Establish connection
         $conn = new mysqli($servername, $username, $password, $dbname);
    
         $s_id = $s_id1;
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
    // Parameters: obj   - the json matchlist object
    //             s_id1 - summoner s_id to repopulate
    // Returns: void
    function repopulate($obj, $s_id1)
    {
         // For MySQL
         $servername = 'localhost';
         $username   = 'jlarobello';
         $password   = 'Ics321808';
         $dbname     = 'leaguestats';
    
         // Establish connection
         $conn = new mysqli($servername, $username, $password, $dbname);
    
         $s_id = $s_id1;
    
         // Delete current rows in wins/losses tables. Utilizes a left join.
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
             populate($matchid, $timestamp, $championid, $s_id); // 1 API request per a call.
             sleep(2);
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
    
    // Name: verify
    // Desc: verifies summoner name
    // Parameters: s_name - summoner name
    //             s_id2  - summoner comparing to
    // Returns: s_id
    function verify($s_name, $s_id2)
    {
         // Start session
         session_start();
    
        // For MySQL
        $servername = 'localhost';
        $username   = 'jlarobello';
        $password   = 'Ics321808';
        $dbname     = 'leaguestats';
    
        // Establish connection
        $conn = new mysqli($servername, $username, $password, $dbname);
    
        $s_id  = 0;
        $url   = 'https://na.api.pvp.net/api/lol/na/v1.4/summoner/by-name/'.rawurlencode($s_name).
                 '?api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e';
        $json  = file_get_contents($url);
        $obj   = json_decode($json, true);
    
        if(empty($obj))
        {
            $s_id = -1;
        }
        else
        {
            $temp = preg_replace('/\s/', '', $s_name);
            $temp = strtolower($temp);
            $s_id = $obj[$temp]['id'];

            if($s_id == $s_id2)
            {
                return -1;
            }
    
            $query     = "select latest_timestamp from stats where s_id = $s_id";
            $result    = $conn->query($query);
            $resultrow = $result->fetch_assoc();
    
            $json       = file_get_contents("https://na.api.pvp.net/api/lol/na/v2.2/matchlist/by-summoner/$s_id?seasons=PRESEASON2016&api_key=9073dedb-d0e0-43db-8557-9ae31bf7967e");
            $obj        = json_decode($json, TRUE);
            $timestamp  = $obj["matches"][0]["timestamp"];
    
            if($result->num_rows > 0 && $timestamp > $resultrow["latest_timestamp"] && !empty($obj))
            {
                repopulate($obj, $s_id);
            }
            else if($result->num_rows == 0)
            {
                if($obj["totalGames"] == 0)
                {
                    header("Location: invalid2.html");
                    $conn->close();
                    die();
                }
    
                for($i = 0; $i < 5; $i++) // max 5 because of API request cap
                {
                    $matchid    = $obj["matches"][$i]["matchId"];
                    $championid = $obj["matches"][$i]["champion"];
                    $timestamp  = $obj["matches"][$i]["timestamp"];
                    populate($matchid, $timestamp, $championid, $s_id); // 1 API request per a call.
                    sleep(2);
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
        }
        return $s_id;
    }
?>
