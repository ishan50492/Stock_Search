<?php

	function dump($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
?>
<?php

		if (isset($_POST['search'])) 
		{
			$name = trim($_POST['name']);
		}
		elseif (isset($_GET['name']))
		{
			$name = trim($_GET['name']);
		}
		else
		{
			$name = "";
		}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>Stock Search</title>
	<style>
	form {
		width: 400px;
		height: 170px;
		margin: auto;
		background-color: lightgrey;
		border: 1px solid #D9D9D9;
	}
	table{
		align: center;
		border-collapse: collapse;
		margin-top: 7px;
	}
	thead{
		background-color: lightgrey;
		text-align: left;
	}
	tbody{
		background-color: #FAFAFA;
	}
	th,td{
		border: 1px solid #CCCCCC;
	}
	.headerclass{
		position: relative;
		font-size:150%; font-style: italic; align:center;
		left:120px;
		font-weight: bold;
		display:inline;
	}
	#noinfo{
		margin-top: 7px;
		border: 1px solid #D9D9D9;
		width: 500px;
		background-color: #F3F3F3;
	}
	.thClass{
		background-color: #F3F3F3;
		text-align: left;
		width:300px;
		font-weight: bold;
	}
	.tdClass{
		background-color: #FAFAFA;
		text-align: center;
		width:300px;
	}
	</style>
	<script>
	'use strict';
	function handleClear()
	{
		var nameField = document.getElementById("name");
		nameField.value="";
		
		var table = document.getElementById("table_id");
		if(table)
		{
			table.parentNode.removeChild(table);
		}
		var noInfo = document.getElementById("noinfo");
		if(noInfo)
		{
			noInfo.parentNode.removeChild(noInfo);
		}
		return false;
	}

	function validateForm() {
    var x = document.forms["myForm"]["name"].value;
    if (x == "") {
        alert("Please Enter Name or Symbol");
        return false;
    }
}
	</script>
</head>
<body>
	<form name="myForm" action="stock.php" method="post">
		<h3 class="headerclass">Stock Search</h3>
		<hr>
		<label for="name" style="position: relative; left:2px; top:10px;">Company Name or Symbol:</label>
		<input type="text" style="position: relative; top:10px;" name="name" id="name" value="<?php echo ($name); ?>" required/><br>
		<input type="submit" name="search" value="Search" style="background-color: white; position: relative; left: 190px; margin-top: 5px; top:12px;" onclick="validateForm();"/>
		<input type="reset" name="clear" value="Clear"  style="background-color:white; position:relative; left:190px; margin-top: 5px; top:12px;" onclick="return handleClear();"/><br>
		<a href="http://www.markit.com/product/markit-on-demand" style="position:relative; left:130px; top:20px;">Powered by Markit on Demand</a>
	</form>
	

	<?php
	function mapValues($value, $percent = false)
	{
		$round = round($value,2);
		if($percent)
			$round .= "%";
		if($round > 0)	
		{
			return $round . "<img src=\"https://img.clipartfest.com/62ba507c1f50b91f93d1c37366aaa18d_up-arrow-image-green-arrow-point-clipart_450-592.png\" height=\"15\" width=\"15\" >";
		}
		else if($round < 0) 
		{
			return $round . "<img src=\"https://img.clipartfest.com/bb7e6e252aca87e259ca7a707f2e17f7_-down-arrow-red-revolution-red-arrow-down_800-800.png\" height=\"15\" width=\"15\" >";
		}
		else	
		{
			return $round;
		}
	}
	function mapDateTime($date_time)
	{
		$tz = new DateTimeZone('America/Los_Angeles');
		$dateTime = new DateTime($date_time);
		
		$dateTime->setTimezone($tz);
		return $dateTime->format('Y-m-d h:i A');
	}
	function mapMarketCap($value)
	{
		$quotient = $value/1000000000;
		$in_billion = round($quotient,2);
		if($in_billion <= 0)
		{
			$quotient = $value/1000000;
			return round($quotient,2)." M";
		}
		else
		{
			return $in_billion." B";
		}
	}
	function mapChangeYTD($lastPrice, $givenChangeYTD)
	{
		
		$diff = $lastPrice - $givenChangeYTD;
		if($diff < 0)
		{
			return "(" . round($diff,2) . ")" . "<img src=\"https://img.clipartfest.com/bb7e6e252aca87e259ca7a707f2e17f7_-down-arrow-red-revolution-red-arrow-down_800-800.png\" height=\"10\" width=\"10\" >";
		}
		else
		{
			return mapValues($diff, false);
		}
	}
	if (isset($_POST['search']))
	{
		$url  = "http://dev.markitondemand.com/";
		$url_page = "MODApis/Api/v2/Lookup/xml";
		$url .= rawurlencode($url_page);
		$url .= "?" . "input=" . urlencode($name);
		
		echo "<div align=\"center\">";
		libxml_use_internal_errors(true);
		$LookupResultList = simplexml_load_file($url);
		if ($LookupResultList === false) 
		{
			foreach (libxml_get_errors() as $error) 
			{
				echo "<div id =\"noinfo\">Line: $error->line($error->column) $error->message </div>";
			}
		} 
		else 
		{
			if($LookupResultList->LookupResult)
			{
				echo "<table border=1 id=\"table_id\" >";
				echo "<thead><tr><th style=\"width:200px;\">Name</th><th>Symbol</th><th>Exchange</th><th>Details</th></tr></thead>";
				echo "<tbody>";
				
				foreach($LookupResultList->LookupResult as $LookupResult)
				{
					$row = "<tr>";
					$row .= "<td style=\"width:60%;\">" . $LookupResult->Name . "</td>";
					$row .= "<td>" . $LookupResult->Symbol . "</td>";
					$row .= "<td>" . $LookupResult->Exchange . "</td>";
					$parameters  = "symbol=" . urlencode($LookupResult->Symbol);
					$parameters .= "&name=" . urlencode($name);
					$row .= "<td>" . "<a href=\"?" .  $parameters ."\" >More Info</a>" . "</td>";
					
					$row .= "</tr>";
					echo $row;
				}
				echo "</tbody>";
				echo "</table>";
			}
			else
			{
				echo "<div id =\"noinfo\">No Records has been found</div>";
			}
		}
		echo "</div>";
	}
		

	if(isset($_GET['symbol']))
	{
		$quote_url  = "http://dev.markitondemand.com/";
		$quote_url_page = "MODApis/Api/v2/Quote/json";
		$quote_url .= rawurlencode($quote_url_page);
		$quote_url .= "?" . "symbol=" . urlencode($_GET['symbol']);
		
		echo "<div align=\"center\">";
		$contents = file_get_contents($quote_url); 
		$contents = utf8_encode($contents); 
		$results = json_decode($contents, true); 
		if (json_last_error() === JSON_ERROR_NONE) 
		{ 
			
			if(strcasecmp($results['Status'], "SUCCESS") == 0)
			{
				echo "<table border=1 id=\"table_id\">";
				echo "<tbody>";
				echo "<tr>	<td class=\"thClass\">Name</td>					<td class=\"tdClass\">" . $results['Name'] . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Symbol</td>				<td class=\"tdClass\">" . $results['Symbol'] . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Last Price</td>			<td class=\"tdClass\">" . $results['LastPrice'] . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Change</td>				<td class=\"tdClass\">" . mapValues($results['Change']) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Change Percent</td>		<td class=\"tdClass\">" . mapValues($results['ChangePercent'],true) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Timestamp</td>			<td class=\"tdClass\">" . mapDateTime($results['Timestamp']) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Market Cap</td>			<td class=\"tdClass\">" . mapMarketCap($results['MarketCap']) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Volume</td>				<td class=\"tdClass\">" . number_format($results['Volume']) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Change YTD</td>			<td class=\"tdClass\">" . mapChangeYTD($results['LastPrice'], $results['ChangeYTD']) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Change Percent YTD</td>	<td class=\"tdClass\">" . mapValues($results['ChangePercentYTD'],true) . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">High</td>					<td class=\"tdClass\">" . $results['High'] . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Low</td>					<td class=\"tdClass\">" . $results['Low'] . "</td>	</tr>";
				echo "<tr>	<td class=\"thClass\">Open</td>					<td class=\"tdClass\">" . $results['Open'] . "</td>	</tr>";
				echo "</tbody>";
				echo "</table>";
				
			}
			elseif(strstr($results['Status'], "Failure") >= 0)
			{
				echo "<div id =\"noinfo\">There is no stock information available</div>";
			}
			else
			{
				echo "<div id =\"noinfo\">Unknown status returned " . $results['Status'] ." </div>";
			}
		} 
		else 
		{ 
			echo "<div id =\"noinfo\">" . json_last_error_msg(). "</div>";
		}
		echo "</div>";
	}
	?>
	<NOSCRIPT>
</body>
</html>