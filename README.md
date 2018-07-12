# PDO_Class
```php
// includes :
include ("./dbdata.inc.php");
require("./PDOcnx.php");

// instanciation :
$db=new PDOcnx($dbdata);

// connecting to the DB :
$db->connexion();
if(!$db->isConnected()){
	echo "Echec connexion :".$db->getPDOError();
}

// passing the PDO object only to another variable :
$hdl=$db->get_handle();

//destroying the handle :
$hdl=null;
unset($hdl);

// using the 'query' method
// includes auto binding
// default fetchstyle =PDO::FETCH_ASSOC
// default result -> fetchall
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie=:serie AND color=:color";
    
$tabparam=array(
		array('serie',$serie),
    array('color',$color,PDO_PARAM_STR)
);

$data=$db->query($req,$tabparam);

if($data===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

// using the 'execute' method
// includes no binding
// default fetchstyle =PDO::FETCH_ASSOC
// default result -> fetchall
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie=? AND color=?";
    
$data=$db->execute($req,[$serie,$color],PDO::FETCH_BOTH);

if($data===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

// IMPORTANT !!
// for SELECT and SHOW queries, 
// both 'query' and 'execute' methods return an array containing 3 arrays
// $data["result"] = containing le result set
// $data["rowcount"] = containing the number of line returned in the result set
// $data["closing] = boolean true -> statement memory has been correctly freed

// for INSERT, UPDATE, DELETE queries,
// both 'query' and 'execute' methods return an array containing 2 arrays
// $data["rowcount"]
// $data["closing"]


// using the 'query' method with statement ONLY
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie > :serie AND color=:color";
    
$tabparam=array(
		array('serie',$serie),
    array('color',$color,PDO_PARAM_STR)
);

$stmt=$db->query($req,$tabparam,,true);

if($stmt===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

while($data=$stmt->fetch(PDO::FETCH_ASSOC)){
	echo $data["model"]."-".$data["serie"]."<br/>";
}

// using the 'execute' method with statement ONLY
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie > :serie AND color=:color";

$stmt=$db->execute($req,array('color'=>$color,'serie'=>$serie),,true);

if($stmt===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

while($data=$stmt->fetch(PDO::FETCH_NUM)){
	echo $data[6]."-".$data[3]."<br/>";
}

// using the 'column_query' method :
// search column can be expressed as a number or as a named field
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie > :serie AND color=:color";
    
$tabparam=array(
		array('serie',$serie),
    array('color',$color)
);

//EITHER :
$col1=$db->column_query($req,$tabparam,2);
// OR :
$col2=$db->column_query($req,$tabparam,"model");
// WILL WORK !

if($col1===false || $col2===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

// using the 'column_execute' method :
// search column can also be expressed as a number or as a named field*
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie > ? AND color=?";

//EITHER :
$col1=$db->column_query($req,[$serie,$color],2);
// OR :
$col2=$db->column_query($req,[$serie,$color],"model");
// WILL WORK !

if($col1===false || $col2===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

// using the 'column_from_dataset' method :
// first make a normal query using query or execute method
// then send the resulting dataset to the column_from_dataset method
$serie=6852;
$color="white";

$req="SELECT * FROM car 
		WHERE serie=:serie AND color=:color";
    
$tabparam=array(
		array('serie',$serie),
    array('color',$color,PDO_PARAM_STR)
);

$data=$db->query($req,$tabparam);

if($data===false){
	$db->closecnx();
	echo "echec requete : ".$db->getPDOError();
	exit();
}

//EITHER :
$col=$db->column_from_dataset($data["result"],2);
// OR :
$col=$db->column_from_dataset($data["result"],"date");
// WILL WORK !

// Transaction methods :
$db->beginTn(); //starts transaction
$db->endTn(); //commits transaction
$db->cancelTn(); // rolls back transaction

// Last inserted ID method :
$db->lastId();

// debug dump method :
$db->debugDump($stmt);
```
**HAVE FUN !!**
