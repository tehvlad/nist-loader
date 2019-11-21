<?php


	session_start();
	
	include("lib/config.php");
	include("lib/connector.php");


	function mres($value)
	{
	    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
	    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

	    return str_replace($search, $replace, $value);
	}


	function xml2js($xmlnode) {
	    $root = (func_num_args() > 1 ? false : true);
	    $jsnode = array();

	    if (!$root) {
	        if (count($xmlnode->attributes()) > 0){
	            $jsnode["$"] = array();
	            foreach($xmlnode->attributes() as $key => $value)
	                $jsnode["$"][$key] = (string)$value;
	        }

	        $textcontent = trim((string)$xmlnode);
	        if (count($textcontent) > 0)
	            $jsnode["_"] = $textcontent;

	        foreach ($xmlnode->children() as $childxmlnode) {
	            $childname = $childxmlnode->getName();
	            if (!array_key_exists($childname, $jsnode))
	                $jsnode[$childname] = array();
	            array_push($jsnode[$childname], xml2js($childxmlnode, true));
	        }
	        return $jsnode;
	    } else {
	        $nodename = $xmlnode->getName();
	        $jsnode[$nodename] = array();
	        array_push($jsnode[$nodename], xml2js($xmlnode, true));
	        return json_encode($jsnode);
	    }
	}   


	function xmlToArray($xml, $options = array()) {
		    $defaults = array(
		        'namespaceSeparator' => ':',//you may want this to be something other than a colon
		        'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
		        'alwaysArray' => array(),   //array of xml tag names which should always become arrays
		        'autoArray' => true,        //only create arrays for tags which appear more than once
		        'textContent' => '$',       //key used for the text content of elements
		        'autoText' => true,         //skip textContent key if node has no attributes or child nodes
		        'keySearch' => false,       //optional search and replace on tag and attribute names
		        'keyReplace' => false       //replace values for above search values (as passed to str_replace())
		    );
		    $options = array_merge($defaults, $options);
		    $namespaces = $xml->getDocNamespaces();
		    $namespaces[''] = null; //add base (empty) namespace
		 
		    //get attributes from all namespaces
		    $attributesArray = array();
		    foreach ($namespaces as $prefix => $namespace) {
		        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
		            //replace characters in attribute name
		            if ($options['keySearch']) $attributeName =
		                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
		            $attributeKey = $options['attributePrefix']
		                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
		                    . $attributeName;
		            $attributesArray[$attributeKey] = (string)$attribute;
		        }
		    }
		 
		    //get child nodes from all namespaces
		    $tagsArray = array();
		    foreach ($namespaces as $prefix => $namespace) {
		        foreach ($xml->children($namespace) as $childXml) {
		            //recurse into child nodes
		            $childArray = xmlToArray($childXml, $options);
		            list($childTagName, $childProperties) = each($childArray);
		 
		            //replace characters in tag name
		            if ($options['keySearch']) $childTagName =
		                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
		            //add namespace prefix, if any
		            if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
		 
		            if (!isset($tagsArray[$childTagName])) {
		                //only entry with this key
		                //test if tags of this type should always be arrays, no matter the element count
		                $tagsArray[$childTagName] =
		                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
		                        ? array($childProperties) : $childProperties;
		            } elseif (
		                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
		                === range(0, count($tagsArray[$childTagName]) - 1)
		            ) {
		                //key already exists and is integer indexed array
		                $tagsArray[$childTagName][] = $childProperties;
		            } else {
		                //key exists so convert to integer indexed array with previous value in position 0
		                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
		            }
		        }
		    }
		 
		    //get text content of node
		    $textContentArray = array();
		    $plainText = trim((string)$xml);
		    if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
		 
		    //stick it all together
		    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
		            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
		 
		    //return node as array
		    return array(
		        $xml->getName() => $propertiesArray
		    );
	}



$xmlNode = simplexml_load_file('./src/800-53-controls-r4.xml');
$arrayData = xmlToArray($xmlNode);




$singleItem = $arrayData['controls']['controls:control'];

$controls = $arrayData;
$countControls = count($singleItem);


	echo "Controles $countControls <hr />";

	// INSERT INTO `control` (`number`, `title`, `family`, `priority`, `baseline_impact`, `description`, `nist_revision`, `nist_function`) VALUES ('TEST2', 'Title', 'Family', 'P5', 'LOW', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sit amet quam pellentesque, blandit ante ut, semper libero. Nullam eget urna mauris. Aliquam dictum molestie varius. Pellentesque viverra fermentum est a varius. Aliquam euismod faucibus odio, at lacinia lectus aliquam sed. Cras vitae nunc eros. Curabitur at felis quis enim tincidunt consectetur. In sed tempor mauris, ac iaculis est. Aenean varius varius quam, nec dictum justo venenatis commodo. Nullam augue mi, imperdiet at ex in, interdum aliquam nunc. Vivamus nec enim metus. Nunc vitae lorem est. Quisque egestas magna eget nisl efficitur, quis rutrum sapien euismod. Mauris consequat velit sed velit consectetur, vel malesuada ligula ultrices. Nunc porta arcu ut ante dictum, in auctor libero dictum.', '800-53', 'Function');


	$sqlInsert = 'INSERT INTO `control` (`number`, `title`, `family`, `priority`, `baseline_impact`, `description`, `nist_revision`, `nist_function`) VALUES (?, ?, ?, ?, ?, ?, "800-53", ?);';

	$args = 's:s:_number;';
	$args .= 's:s:_title;';
	$args .= 's:s:_family;';
	$args .= 's:s:_priority;';
	$args .= 's:s:_baseline;';
	$args .= 's:s:_description;';
	$args .= 's:s:_function';

 
	$mysql = new mysqlConnector(_dbHost, _dbName, _dbUser, _dbPass );

	try {

			$mysql->execQuery('root', "truncate table control", '');
			$mysql->execQuery('root', "truncate table control_statement", ''); 
			$mysql->execQuery('root', "truncate table control_related", ''); 
			$mysql->execQuery('root', "truncate table control_reference", ''); 
		 
	} catch(Exception $e) {
		echo 'Caught exception: '.  $e->getMessage() . " \n";
		 
	}


	$sql = "";

	echo "<pre>";
	for($i=0; $i<$countControls; $i++) {

		try {
				$currentMainControl = $singleItem[$i]['number'];

				echo $i . "-" . $singleItem[$i]['family'] . ' ' . $singleItem[$i]['number'] .  ' ' . $singleItem[$i]['title']  . ' ' . $singleItem[$i]['priority'] . "\n";

				$_SESSION['_number'] = $singleItem[$i]['number'];
				$_SESSION['_title'] = $singleItem[$i]['title'];
				$_SESSION['_family'] = $singleItem[$i]['family'];
				$_SESSION['_priority'] = $singleItem[$i]['priority'];

				if (is_array($singleItem[$i]['baseline-impact'])) {
						$_SESSION['_baseline'] = $singleItem[$i]['baseline-impact'][0];
				} else {
						$_SESSION['_baseline'] = $singleItem[$i]['baseline-impact'];
				}
				
				

				if (is_array($singleItem[$i]['statement']['statement'])) {

						$descText = $singleItem[$i]['statement']['description'] . "\n";;
						$sqlStat = '';
						$sqlSub =  '';

						$hasSubStat = false;


						$cDesc = count($singleItem[$i]['statement']['statement']);

						for($j=0; $j<$cDesc; $j++) {
							$orderNumber = $j+1;
							$descText .= $singleItem[$i]['statement']['statement'][$j]['description'] . "\n";

							$cn = $singleItem[$i]['statement']['statement'][$j]['number'];
							$statement = mres($singleItem[$i]['statement']['statement'][$j]['description']);
							$parent = $singleItem[$i]['number'];


							


							if (is_array($singleItem[$i]['statement']['statement'][$j]['statement'])) {

								$subStatement = $singleItem[$i]['statement']['statement'][$j]['statement'];

								$deepStatement = $singleItem[$i]['statement']['statement'][$j]['statement'];

								$cS = count($subStatement);
								

								for($k-0;$k<$cS;$k++) {


									

									$subOrder = $k + 1;

									$subStText = mres($deepStatement[$k]['description']);

									$subNum = $deepStatement[$k]['number'];

									$sqlSub .= "INSERT INTO `control_statement` (`id_statement`, `control_number`, `parent_control_number`, `description`, `is_gen_relevant`, `st`, `order_statement`) VALUES (NULL, '$subNum', '$cn', '$subStText', '0', '1', $subOrder);\n";


									$subNum .= "\t\t $subStText\n";
								}

								$hasSubStat = true;

								

							} 


							$sqlStat .= "INSERT INTO `control_statement` (`id_statement`, `control_number`, `parent_control_number`, `description`, `is_gen_relevant`, `st`, `order_statement`) VALUES (NULL, '$cn', '$parent', '$statement', '0', '1', $orderNumber);\n";

						}


						$_SESSION['_description'] = mres($descText);

						$mysql->execScript('root', $sqlStat);
						//if ($hasSubStat) {
						 if (strlen($sqlSub) > 0) {
							$mysql->execScript('root', $sqlSub);
						}


				} else {
						$_SESSION['_description'] = $singleItem[$i]['statement']['description']; 
				}


				if (is_array($singleItem[$i]['supplemental-guidance']['related'])) {

					$sqlStat = '';


						$cDesc = count($singleItem[$i]['supplemental-guidance']['related']);

						for($j=0; $j<$cDesc; $j++) {
							$orderNumber = $j+1;
							$cn = $singleItem[$i]['supplemental-guidance']['related'][$j];
							$parent = $singleItem[$i]['number'];
							$sqlStat .= "INSERT INTO `control_related` (`id_related`, `control_number`, `control_related`, `st`, order_related) VALUES (NULL, '$parent', '$cn', '1', $orderNumber);\n";
						}

						$mysql->execScript('root', $sqlStat);
				}


				//references
				if (is_array($singleItem[$i]['references']['reference'])) {

					$sqlStat = '';


						
						$arrayReference = $singleItem[$i]['references']['reference'];
						$cDesc = count($arrayReference );


						if ($cDesc == 1) {


								$href = $arrayReference['item']['@href'];
								$descriptionUrl = $arrayReference['item']['$'];
							 
								$sqlStat .= "INSERT INTO `control_reference` (`id_reference`, `control_number`, `href`, `description`, `order_reference`, `st`) VALUES (NULL, '$currentMainControl', '$href', '$descriptionUrl ', '1', '1');\n";

						} else {

							for($j=0; $j<$cDesc; $j++) {
								$orden = $j+1;
								 

								 if (count($arrayReference[$j]['item'])>1) {

								 	$href = $arrayReference[$j]['item']['@href'];
									$descriptionUrl = $arrayReference[$j]['item']['$'];
								 
									$sqlStat .= "INSERT INTO `control_reference` (`id_reference`, `control_number`, `href`, `description`, `order_reference`, `st`) VALUES (NULL, '$currentMainControl', '$href', '$descriptionUrl ', '$orden', '1');\n";

								 } else  {
								 	$href = '#';
									$descriptionUrl = $arrayReference[$j]['item'];
								 
									$sqlStat .= "INSERT INTO `control_reference` (`id_reference`, `control_number`, `href`, `description`, `order_reference`, `st`) VALUES (NULL, '$currentMainControl', '$href', '$descriptionUrl ', '$orden', '1');\n";

								 }
								
							}

						}


						

						$mysql->execScript('root', $sqlStat);
				}



				// Add more data
				$_SESSION['_function'] = 'TDB';

				$idIbns = $mysql->execQuery('root', $sqlInsert, $args);

				// echo $i . "-" . $singleItem[$i]['family'] . ' ' . $singleItem[$i]['number'] .  ' ' . $singleItem[$i]['title']  . ' ' . $singleItem[$i]['priority'] . " [$idIbns]\n";

			} catch(Exception $e) {
				echo 'Caught exception: '.  $e->getMessage() . " $i \n";
				echo $i . "-" . $singleItem[$i]['family'] . ' ' . $singleItem[$i]['number'] .  ' ' . $singleItem[$i]['title']  . ' ' . $singleItem[$i]['priority'] . " [$idIbns]\n";
				echo print_r($_SESSION, true);
			}

		//$sql .= "INSERT INTO `control` (`number`, `title`, `family`, `priority`, `baseline_impact`, `description`, `nist_revision`, `nist_function`) VALUES (?, ?, ?, ?, ?, ?, "800-53", ?)\n";

	}

	echo "<hr /> $sql";

	echo "</pre>";

	// $xml_string = file_get_contents("./src/800-53-controls-r4.xml")
	// $xml = simplexml_load_string($xml_string);
	// $array = xml2js($xml);
	// $json = json_encode($xml);
	// $array = json_decode($json,TRUE);
	// echo "<pre>" . htmlentities($xml_string) . "</pre>";
	// echo "<hr />";

	//echo "<pre>" . print_r($json , true) . "</pre>";
	
	echo "<hr />";
	echo "<pre>" . print_r($singleItem, true) . "</pre>";

// 	echo "<hr />";

	// echo "<pre>" . print_r($arrayData, true) . "</pre>";


	session_destroy();


?>