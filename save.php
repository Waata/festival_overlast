<?php

date_default_timezone_set('europe/amsterdam');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $event_caused = ($_POST['event_caused'] == 'true') ? 1 : 0;
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $datetime = !empty($_POST['datetime']) ? $_POST['datetime'] : date("Y-m-d H:i:s");
    $email = $_POST['email'];
    $types = $_POST['types'];
    $noise_types = $_POST['noise_types'];
    $noise_indoor = $_POST['noise_indoor'];
    
    $result = array();
    
    // ToDo: Check if all fields are set and valid.
    // Assume they are all valid. UNSAFE!
        
    // Store in database
    $mysqli = new mysqli("localhost", "festi_nl_festiv2", "zsHtJMe888H3", "festivaloverlast_nl_festiva");
    if ($mysqli->connect_errno) {
        //echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        $result['status'] = 'error';
        $result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    } else {
        // Insert new record
        if (!($stmt = $mysqli->prepare("".
            "INSERT INTO `complaints` (`datetime`, `latitude`, `longitude`, ".
            "`email`, `event_caused`, `types`, `noise_types`, `noise_indoor`) ".
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?);"))) 
        {
            //echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            $result['status'] = 'error';
            $result['message'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        } else {
            if (!$stmt->bind_param("sddsisss", $datetime, $latitude, $longitude, $email, $event_caused, $types, $noise_types, $noise_indoor)) {
                //echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                $result['status'] = 'error';
                $result['message'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            } else {
                if (!$stmt->execute()) {
                    //echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    $result['status'] = 'error';
                    $result['message'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                } else {
                    $stmt->close();
                    
                    $result['status'] = 'ok';
                    
                    // Insert new record
                    if (!($stmt = $mysqli->prepare(" ".
                        "SELECT `name`, `address`, `city`, `latitude`, `longitude`, ".
                        "111.1111 ".
                        "	* DEGREES(ACOS(COS(RADIANS(Latitude)) ".
                        "	* COS(RADIANS(?)) ".
                        "    * COS(RADIANS(Longitude - ?)) ".
                        "    + SIN(RADIANS(Latitude)) ".
                        "    * SIN(RADIANS(?)))) AS distance ".
                        "FROM `festivals` ".
                        "WHERE `datetime_start` <= ? AND `datetime_end` > ? ".
                        "AND 111.1111 ".
                        "	* DEGREES(ACOS(COS(RADIANS(Latitude)) ".
                        "	* COS(RADIANS(?)) ".
                        "	* COS(RADIANS(Longitude - ?)) ".
                        "	+ SIN(RADIANS(Latitude)) ".
                        "	* SIN(RADIANS(?)))) < 5 ".
                        "ORDER BY `distance` ASC"))) 
                    {
                        $result['status'] = 'error';
                        $result['message'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
                    } else {
                        if (!$stmt->bind_param("dddssddd", $latitude, $longitude, $latitude, $datetime, $datetime, $latitude, $longitude, $latitude)) {
                            $result['status'] = 'error';
                            $result['message'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                        } else {
                            if (!$stmt->execute()) {
                                $result['status'] = 'error';
                                $result['message'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                            } else {
                                $result['status'] = 'ok';
                                
                                $stmt->bind_result($name, $address, $city, $latitude, $longitude, $distance);

                                /* fetch values */
                                $events = array();
                                while ($stmt->fetch()) {
                                    $event = array('name' => $name, 'address' => $address, 'city' => $city, 'latitude' => $latitude, 'longitude' => $longitude, 'distance' => $distance);
                                    array_push($events, $event);
                                }
                                $result['events'] = $events;
                                
                                $stmt->close();
                            }
                        }
                    }
                }
            }
        }
    }
    
    echo json_encode($result);
}

?>