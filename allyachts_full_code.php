<?php

//**** allowing suffisant memory to avoid memory errors ********
ini_set('memory_limit', '3024M');


//*********** Importing Data from the api to a compressed file named "testing2" ********************

$url='http://ws.nausys.com/CBMS-external/rest/catalogue/v6/yachts/9870890';
$user_name = 'rest@MEDIS';
$password = 'hakimhakim';
$data = new \stdClass();
$data-> username = $user_name;
$data-> password = $password;

$myjson = json_encode($data);

$fp=fopen('testing2','a+');

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $myjson);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TRANSFER_ENCODING, 1);
curl_setopt($ch, CURLOPT_ENCODING , "gzip");
curl_setopt($ch, CURLOPT_FILE, $fp);

curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_exec($ch);


curl_close($ch);
fclose($fp);


//************ decompression of "testing2" and transmitting data to a new Json file ************



$fp=fopen('yachts.json','a+');

$sfp = gzopen('testing2', "r");

while ($line = fgets($sfp)) {
    fwrite($fp,$line);
}

fclose($sfp);
fclose($fp);


//*********** Connection to database "all yachts " and creation of table "yachts" *******


$servername = "localhost";
$username = "root";
$password = "";
$db='allyachts';
$conn = new mysqli($servername, $username, $password, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}





$sql = "CREATE TABLE Yachts (
    id int ,
    NauSysID int PRIMARY KEY ,
    yacht_Name varchar(255)
    )";
    
    if ($conn->query($sql) === TRUE) {
      echo "Table Yachts created successfully";
    } else {
      echo "Error creating table: " . $conn->error;
    }



//*********** Inserting data from yachts.json to the database table "yachts" ***************


spl_autoload_register(require 'C:wamp64/apps/json-machine-master/src/autoloader.php');
use JsonMachine\Items;

$elements =Items::fromFile('yachts.json'); 



$compteur=1;

$stmt = $conn->prepare("INSERT INTO yachts (id,NauSysID,yacht_name) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $id, $NauSysID,$yacht_name);

foreach ($elements as $key => $value)
{   
    
    if ($key == "yachts") {
        foreach($value as $yacht) {
            
            $id=$compteur;
            $NauSysID=$yacht->id;
            $yacht_name=$yacht->name;
            $stmt->execute();

            $compteur++;
        }
        break;
    }   


}










?>