<?php

session_start();
ob_start();

/*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

2021-12-29 = Updated to replace depreciated code and a little added style updates

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/

?>
<style type="text/css">
	a, a:link, a:visited{
		color: #4400cc;
		text-decoration: none;
	}

	a:hover {
		text-decoration: underline;
	}
</style>
<?php
## pass entry window

if(!empty($_SESSION['server']) || !empty($_SESSION['user']) || !empty($_SESSION['pass'])){
	$conn = new mysqli($_SESSION['server'], $_SESSION['user'], $_SESSION['pass']);
}

# !mysql_connect($_SESSION['server'],,)

if((empty($_SESSION['server']) || empty($_SESSION['user'])) && empty($_POST)){

	?><center>Please enter the database connections information to enter this section:<br /><br />
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table width="50%">
	<tr><td>Server:</td><td><input type="text" name="server" value="Localhost"></td></tr>
	<tr><td>Username:</td><td><input type="text" name="user"></td></tr>
	<tr><td>Password:</td><td><input type="password" name="pass"></td></tr>
	<tr><td colspan="2" style="text-align: center"><input type="submit" value="Enter"></td></tr></form></center><?php

} else {

# Connect to db

	if(!empty($_POST['server'])){
	$conn = new mysqli($_POST['server'], $_POST['user'], $_POST['pass']);

	if ($conn->connect_errno > 0) header("location: ?");

	$_SESSION['server'] = $conn->real_escape_string($_POST['server']);
	$_SESSION['user'] = $conn->real_escape_string($_POST['user']);
	$_SESSION['pass'] = $conn->real_escape_string($_POST['pass']);
	header("location: ?");
	exit();
	}

	$conn = new mysqli($_SESSION['server'], $_SESSION['user'], $_SESSION['pass']);

echo "<a href=\"?view=logout\">Logout</a><br /><br />";
	
## Create Nav
	
if(!empty($_GET['tbln'])){
		$nav = "<a href=\"?\">Root</a> > <a href=\"?view=db&dbn=".$_GET['dbn']."\">".$_GET['dbn']."</a> > <a href=\"?view=tbl&dbn=".$_GET['dbn']."&tbln=".$_GET['tbln']."\">".$_GET['tbln']."</a><br /><br /><form action=\"?view=viewsql&dbn=".$_GET['dbn']."&tbln=".$_GET['tbln']."\" method=\"post\">";
	} else {
		$nav = "<a href=\"?\">Root</a> > <a href=\"?view=db&dbn=".$_GET['dbn']."\">".$_GET['dbn']."</a> -> <a href=\"?view=viewsql&dbn=".$_GET['dbn']."\">SQL</a><br /><br /><form action=\"?view=viewsql&dbn=".$_GET['dbn']."\" method=\"post\">";
	}
	
## Check Views

switch(@$_GET['view']){

default:

	$db_list = $conn->query("Show Databases");

	foreach ($db_list as $row) {
		echo "<a href=\"?view=db&dbn=".$row['Database']."\">".$row['Database']."</a><br />";
	}

break;

case 'logout':
	unset($_SESSION['server']);
	unset($_SESSION['user']);
	unset($_SESSION['pass']);
	header("location: ?");
break;

case 'db':

	echo "<a href=\"?\">Root</a> > $_GET[dbn] -> <a href=\"?view=viewsql&dbn=$_GET[dbn]\">SQL</a><br /><br />";

	$conn->select_db($_GET['dbn']);
	$tablesresult = $conn->query("SHOW TABLES FROM $_GET[dbn]");
	echo"
		<table align=\"center\">
			<tr>
				<td width=\"20%\" align=\"center\">Table</td>
				<td width=\"20%\" align=\"center\">Fields</td>
				<td width=\"20%\" align=\"center\">Rows</td>
				<td width=\"20%\" align=\"center\">Structure</td>
				<td width=\"20%\" align=\"center\">Browse</td>
			</tr>
	";
	foreach ($tablesresult as $tbl){

		$tblname = $tbl['Tables_in_'.$_GET['dbn']];

		$q = $conn->query("SHOW COLUMNS FROM ".$tblname."");
		$result = $q->num_rows;
		$q = $conn->query("select * from ".$tblname."");
		$rows = $q->num_rows;
		echo "<tr>";
			echo "<td align=\"center\">".$tblname."</a></td>";
			echo "<td align=\"center\">$result</td>";
			echo "<td align=\"center\">$rows</td>";
			echo "<td align=\"center\">[<a href=\"?view=tbl&dbn=".$_GET['dbn']."&tbln=".$tblname."&type=str\">X</a>]</td>";
			echo "<td align=\"center\">[<a href=\"?view=tbl&dbn=".$_GET['dbn']."&tbln=".$tblname."&type=bro\">X</a>]</td>";
		echo "</tr>";
	}
	echo "</table>";

break;

case 'viewsql':

		#mysql_select_db($_GET['dbn']);

		$_POST['sql'] = @str_replace("\'", "'", $_POST['sql']);
		$_POST['sql'] = @str_replace("\\\"", "\"", $_POST['sql']);

	echo $nav;

	echo "<div align=\"center\">BTW: Joining stuff won't show up:<br /><textarea name=\"sql\" cols=\"100\" rows=\"10\">".$_POST['sql']."</textarea><br /><br /><input type=\"submit\" value=\"Run Query\"></div>";

	echo "</form>";


		$sql = $_POST['sql'];
		if(strstr($sql, "SELECT") || strstr($sql, "select")){

		$conn->select_db($_GET['dbn']);
		$sql = $conn->query($_POST['sql']) or die($conn->error);
		$row = $sql->fetch_array();
		$rows = array_keys($row);
		$num = $sql->field_count;
		$n=0;
		while($n <= $num){
			$col[$n] = $rows[$n];
			$n++;
		}
		$colnum=round(100/$num);
		echo "<table align=\"center\">
		<tr>";
			$i=0;
			while($i < $num){
				echo "<td width=\"$colnum\" align=\"center\">$col[$i]</td>";
				$i++;
			}
		echo "</tr>";
		$sql = $conn->query($_POST['sql']);
		while($row = $sql->fetch_array()){
			echo "<tr>";
			$i=0;
			while($i < $num){
				$colname = $col[$i];
				echo "<td align=\"center\">$row[$colname]</td>";
				$i++;
			}
		echo "</tr>";
		}
		echo "</table>";
		} else {
			#echo "Request Not Carried Out";
		}

break;

case 'sql':

	$conn->select_db($_GET['dbn']);

	echo $nav;

	echo "<div align=\"center\"><textarea name=\"sql\" cols=\"100\" rows=\"10\">SELECT * FROM ".$_GET['tbln']."</textarea><br /><br /><input type=\"submit\" value=\"Run Query\"></div>";

	echo "</form>";

break;

case 'tbl':

	$conn->select_db($_GET['dbn']);

	echo "<a href=\"?\">Root</a> > <a href=\"?view=db&dbn=".$_GET['dbn']."\">".$_GET['dbn']."</a> > $_GET[tbln]<br /><br />";
	echo "<center><a href=\"?view=tbl&dbn=".$_GET['dbn']."&tbln=".$_GET['tbln']."&type=str\">Structure</a> | <a href=\"?view=tbl&dbn=".$_GET['dbn']."&tbln=".$_GET['tbln']."&type=bro\">Browse</a> | <a href=\"?view=sql&dbn=".$_GET['dbn']."&tbln=".$_GET['tbln']."\">SQL</a></center><br /><br />";
	switch(@$_GET['type']){

	case 'str':
	default:

		$result = $conn->query("SHOW COLUMNS FROM ".$_GET['tbln']);
		if ($result->num_rows > 0) {
			echo "
				<table align=\"center\">
					<tr>
						<td width=\"16%\" align=\"center\">Field</td>
						<td width=\"16%\" align=\"center\">Type</td>
						<td width=\"16%\" align=\"center\">Null</td>
						<td width=\"16%\" align=\"center\">Key</td>
						<td width=\"16%\" align=\"center\">Default</td>
						<td width=\"16%\" align=\"center\">Extra</td>
					</tr>";
			foreach ($result as $row){
				echo "<tr>";
					echo "<td align=\"center\">".$row['Field'] . "</td>";
					echo "<td align=\"center\">".$row['Type'] . "</td>";
					echo "<td align=\"center\">".$row['Null'] . "</td>";
					echo "<td align=\"center\">".$row['Key'] . "</td>";
					echo "<td align=\"center\">".$row['Default'] . "</td>";
					echo "<td align=\"center\">".$row['Extra'] . "</td>";
				echo "</tr>";
			}
			echo "</table>";
		}

	break;

	case 'bro':

		$result = $conn->query("SHOW COLUMNS FROM ".$_GET['tbln']);
		$num = $result->num_rows;
		foreach ($result as $row){
			$col[] = $row['Field'];
		}
		$colnum=round(100/$num);
		echo "<table align=\"center\">
		<tr>";
			$i=0;
			while($i < $num){
				echo "<td width=\"".$colnum."\" align=\"center\">".$col[$i]."</td>";
				$i++;
			}
		echo "</tr>";
		$sql = $conn->query("select * from ".$_GET['tbln']);
		foreach ($sql as $row){
			echo "<tr>";
			$i=0;
			while($i < $num){
				$colname = $col[$i];
				echo "<td align=\"center\">".$row[$colname]."</td>";
				$i++;
			}
		echo "</tr>";
		}
		echo "</table>";

	break;

	}

break;

}
}

ob_end_flush();

?>
