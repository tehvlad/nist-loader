<?php

	
	require_once('config.php');
	
	error_reporting(E_ERROR | E_PARSE);

    function validateDate($date, $format){

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;

    }

    function now(){

        $timezone  = -5; 
        return gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I"))); 

    }
    

    function multiimplode($glue,$array){

     $result = implode($glue , array_map(function ($v, $k) {return $k.'='."'".$v."'";}, $array, array_keys($array) ));

     return $result;

    }

    function parseDates($_date) {
          
       $replace = array('a','m','p','.');
       $date = str_replace($replace, '', $_date);

        $formatos = array();

        $delimiters = Array("/","-");

        $res = multiexplode($delimiters,$date);

        $d = $res[0][0];
        $m = $res[1][0];
        $_y = $res[2][0];

        $_e = explode(' ', $_y);
        $y = $_e[0];
        $h = $_e[1];

        $long = strlen($y);

        if ($long == 2) {
            $date = $d.'/'.$m.'/'.'20'.$y.' '.$h;
            $y = '20'.$y;
        }
        
        $formatos[] = 'd-m-Y';
        $formatos[] = 'd/m/Y';
        $formatos[] = 'd/m/Y ';
        $formatos[] = 'd-m-Y H:i';
        $formatos[] = 'dmY H:i';
        // $formatos[] = 'dmY Hi';
        // $formatos[] = 'd/mY H:i';
        $formatos[] = 'dm/Y H:i';
        $formatos[] = 'd/m/Y H:i';
        $formatos[] = 'd/m/Y Hi';
        $formatos[] = 'd-m-Y Hi';
        $formatos[] = 'd-m-Y H:i:s';
        $formatos[] = 'd/m/Y H:i:s';
        $formatos[] = 'd-m-Y H:i:s ';
        $formatos[] = 'd/m/Y H:i:s ';

        $cFormatos = count($formatos);

        for($cf = 0; $cf <$cFormatos; $cf++) {
            $_sampleDate = date_create_from_format($formatos[$cf], $date);

            // $date = '';
            if ($_sampleDate != false) {                                                       
                $date = $_sampleDate->format('Y-m-d H:i:s');
            }

            // $valida = validateDate($date,'Y-m-d H:i:s');

            // if ($valida <> 1) {
            //     $date = 0;
            // }
        }

        return $date;

    }


    function sqlUpdate($arraySet,$arrayWhere,$table, $level) {

        $set = $arraySet;
        $where = $arrayWhere;

        if ($level) {$set = remove_level($array);}
      
        $records = array_keys($set[0]);
        $record = implode(', ', $records); 

        $totalRecords = count($set);

        $_sqlUpdate = '';
        
        for ($i=0; $i < $totalRecords; $i++) { 

            $_sqlUpdate.= "update ".$table." set ";
            $_sqlUpdate.= multiimplode(', ',$set[$i]);
            $_sqlUpdate.= " where ";
            $_sqlUpdate.= multiimplode(' and ',$where[$i]);
            $_sqlUpdate.= ";\n";

        }
 
      $result = $_sqlUpdate; 
      
      return $result;

    }
    

    function sqlReplace($array,$table,$level) {

        $arrayReplace = $array;

        if ($level) {$arrayReplace = remove_level($array);}
      
        $records = array_keys($arrayReplace[0]);
        $record = implode(', ', $records); 

        $totalRecords = count($arrayReplace);

        $_sqlReplace = '';
        
        for ($i=0; $i < $totalRecords; $i++) { 
            $_sqlReplace.= ' insert into '.$table.' (';
            $_sqlReplace.= $record;
            $_sqlReplace.= ")values\n";
            $_sqlReplace.= "('";
            $_sqlReplace.= implode("', '", $arrayReplace[$i])."') on duplicate key \n";
            $_sqlReplace.= "update \n";
            $_sqlReplace.= multiimplode(', ',$arrayReplace[$i]);
            $_sqlReplace.= ";\n";                       
        }
 
      $result = $_sqlReplace; 
      
      return $result;

    }

    function sqlInsert($array,$table,$level) {

            $arrayInsert = $array;

            if ($level) {
                $arrayInsert = remove_level($array);
            }
          
            $records = array_keys($arrayInsert[0]);
            $record = implode(',', $records);

            $totalRecords = count($arrayInsert);
            $coma = $totalRecords-1;

            $_sqlInsert = '';
            $_sqlInsert.= ' insert into '.$table.' (';
            $_sqlInsert.= $record;
            $_sqlInsert.= ")values\n";

            for ($i=0; $i < $totalRecords; $i++) { 
                $_sqlInsert.= "('";
                $_sqlInsert.= implode("', '", $arrayInsert[$i])."')";

                if ($i<>$coma) {
                    $_sqlInsert.= ",\n";
                }
                    
            }

            $_sqlInsert.= ";";
          
          $result = $_sqlInsert; 
          
          return $result;
    }

    function remove_level($array) {
          $result = array();
          foreach ($array as $key => $value) {
            if (is_array($value)) {
              $result = array_merge($result, $value);
            }
          }
          return $result;
    }

	/* Source http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string */
	function crypto_rand_secure($min, $max) {
		$range = $max - $min;
		if ($range < 0) return $min; // not so random...
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
	}

	function getToken($length){
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        // $codeAlphabet.= "#-$%&#-$%&#-$%&#-$%&#-$%&#-$%&";
		$codeAlphabet.= "0123456789";
		for($i=0;$i<$length;$i++){
			$token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
		}
		return $token;
	}

	function getOS($user_agent = null)
{
    if(!isset($user_agent) && isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    // https://stackoverflow.com/questions/18070154/get-operating-system-info-with-php
    $os_array = [
        'windows nt 10'                              =>  'Windows 10',
        'windows nt 6.3'                             =>  'Windows 8.1',
        'windows nt 6.2'                             =>  'Windows 8',
        'windows nt 6.1|windows nt 7.0'              =>  'Windows 7',
        'windows nt 6.0'                             =>  'Windows Vista',
        'windows nt 5.2'                             =>  'Windows Server 2003/XP x64',
        'windows nt 5.1'                             =>  'Windows XP',
        'windows xp'                                 =>  'Windows XP',
        'windows nt 5.0|windows nt5.1|windows 2000'  =>  'Windows 2000',
        'windows me'                                 =>  'Windows ME',
        'windows nt 4.0|winnt4.0'                    =>  'Windows NT',
        'windows ce'                                 =>  'Windows CE',
        'windows 98|win98'                           =>  'Windows 98',
        'windows 95|win95'                           =>  'Windows 95',
        'win16'                                      =>  'Windows 3.11',
        'mac os x 10.1[^0-9]'                        =>  'Mac OS X Puma',
        'macintosh|mac os x'                         =>  'Mac OS X',
        'mac_powerpc'                                =>  'Mac OS 9',
        'linux'                                      =>  'Linux',
        'ubuntu'                                     =>  'Linux - Ubuntu',
        'iphone'                                     =>  'iPhone',
        'ipod'                                       =>  'iPod',
        'ipad'                                       =>  'iPad',
        'android'                                    =>  'Android',
        'blackberry'                                 =>  'BlackBerry',
        'webos'                                      =>  'Mobile',

        '(media center pc).([0-9]{1,2}\.[0-9]{1,2})'=>'Windows Media Center',
        '(win)([0-9]{1,2}\.[0-9x]{1,2})'=>'Windows',
        '(win)([0-9]{2})'=>'Windows',
        '(windows)([0-9x]{2})'=>'Windows',

        // Doesn't seem like these are necessary...not totally sure though..
        //'(winnt)([0-9]{1,2}\.[0-9]{1,2}){0,1}'=>'Windows NT',
        //'(windows nt)(([0-9]{1,2}\.[0-9]{1,2}){0,1})'=>'Windows NT', // fix by bg

        'Win 9x 4.90'=>'Windows ME',
        '(windows)([0-9]{1,2}\.[0-9]{1,2})'=>'Windows',
        'win32'=>'Windows',
        '(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})'=>'Java',
        '(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}'=>'Solaris',
        'dos x86'=>'DOS',
        'Mac OS X'=>'Mac OS X',
        'Mac_PowerPC'=>'Macintosh PowerPC',
        '(mac|Macintosh)'=>'Mac OS',
        '(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'=>'SunOS',
        '(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'=>'BeOS',
        '(risc os)([0-9]{1,2}\.[0-9]{1,2})'=>'RISC OS',
        'unix'=>'Unix',
        'os/2'=>'OS/2',
        'freebsd'=>'FreeBSD',
        'openbsd'=>'OpenBSD',
        'netbsd'=>'NetBSD',
        'irix'=>'IRIX',
        'plan9'=>'Plan9',
        'osf'=>'OSF',
        'aix'=>'AIX',
        'GNU Hurd'=>'GNU Hurd',
        '(fedora)'=>'Linux - Fedora',
        '(kubuntu)'=>'Linux - Kubuntu',
        '(ubuntu)'=>'Linux - Ubuntu',
        '(debian)'=>'Linux - Debian',
        '(CentOS)'=>'Linux - CentOS',
        '(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'=>'Linux - Mandriva',
        '(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'=>'Linux - SUSE',
        '(Dropline)'=>'Linux - Slackware (Dropline GNOME)',
        '(ASPLinux)'=>'Linux - ASPLinux',
        '(Red Hat)'=>'Linux - Red Hat',
        // Loads of Linux machines will be detected as unix.
        // Actually, all of the linux machines I've checked have the 'X11' in the User Agent.
        //'X11'=>'Unix',
        '(linux)'=>'Linux',
        '(amigaos)([0-9]{1,2}\.[0-9]{1,2})'=>'AmigaOS',
        'amiga-aweb'=>'AmigaOS',
        'amiga'=>'Amiga',
        'AvantGo'=>'PalmOS',
        //'(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1}-([0-9]{1,2}) i([0-9]{1})86){1}'=>'Linux',
        //'(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1} i([0-9]{1}86)){1}'=>'Linux',
        //'(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1})'=>'Linux',
        '[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})'=>'Linux',
        '(webtv)/([0-9]{1,2}\.[0-9]{1,2})'=>'WebTV',
        'Dreamcast'=>'Dreamcast OS',
        'GetRight'=>'Windows',
        'go!zilla'=>'Windows',
        'gozilla'=>'Windows',
        'gulliver'=>'Windows',
        'ia archiver'=>'Windows',
        'NetPositive'=>'Windows',
        'mass downloader'=>'Windows',
        'microsoft'=>'Windows',
        'offline explorer'=>'Windows',
        'teleport'=>'Windows',
        'web downloader'=>'Windows',
        'webcapture'=>'Windows',
        'webcollage'=>'Windows',
        'webcopier'=>'Windows',
        'webstripper'=>'Windows',
        'webzip'=>'Windows',
        'wget'=>'Windows',
        'Java'=>'Unknown',
        'flashget'=>'Windows',

        // delete next line if the script show not the right OS
        //'(PHP)/([0-9]{1,2}.[0-9]{1,2})'=>'PHP',
        'MS FrontPage'=>'Windows',
        '(msproxy)/([0-9]{1,2}.[0-9]{1,2})'=>'Windows',
        '(msie)([0-9]{1,2}.[0-9]{1,2})'=>'Windows',
        'libwww-perl'=>'Unix',
        'UP.Browser'=>'Windows CE',
        'NetAnts'=>'Windows',
    ];

    // https://github.com/ahmad-sa3d/php-useragent/blob/master/core/user_agent.php
    $arch_regex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
    $arch = preg_match($arch_regex, $user_agent) ? '64' : '32';

    foreach ($os_array as $regex => $value) {
        if (preg_match('{\b('.$regex.')\b}i', $user_agent)) {
            return $value.' x'.$arch;
        }
    }

    return 'Unknown';
}

	function multiexplode ($delimiters,$string) {
	    $ary = explode($delimiters[0],$string);
	    array_shift($delimiters);
	    if($delimiters != NULL) {
	        foreach($ary as $key => $val) {
	             $ary[$key] = multiexplode($delimiters, $val);
	        }
	    }
	    return  $ary;
	}
	

	function sessionTerminated(){
		if(!isset( $_SESSION[_prefix . '_idUsuario'])) {
	    echo "<script>send('#busquedaCriterioMenu', 'cerrarSesion', '#scriptAreaPostData');</script>";
	      exit;
	    }
	}

	function successAlert() {

		$script = "<script>";
    	$script.= "$('#success-alert').show();";
    	$script.= "setTimeout(function() { $('#success-alert').hide(); }, 3000);";
    	$script.= "</script>";

    	return $script;
    }
	    

	function crearTablaNormal($originalData, $extra, $header) {
		
		$rec = array();
        $cols = count($originalData[0]);
        $rows = count($originalData); 
        $dataCols = $originalData[0];
        $data = array();
        $head = array_keys($originalData[0]);

        $dataPrint = array();
        $dataRow = array();

        // Generamos arreglos, uno para impresion, el 2do para rowspans
        for($i=0; $i<$rows;$i++) {
            for($j=0; $j<$cols;$j++) {       
                $dataPrint[$i][$j] = true;
                $dataRow[$i][$j] = 1;
            }
            // Conversion a indice numerico
            $j = 0;
            foreach($dataCols as $key => $itemValue) {            
                $data[$i][$j] = $originalData[$i][$key];
                $j++;
            }
        }

        for($i=0; $i<$rows;$i++) {         
            for($j=0; $j<$cols;$j++) {
                // Solo prueba en caso de que no haya sido descartado aun el valor
                if ($dataPrint[$i][$j]) {
                    $testItem = $data[$i][$j];
                }
            }           
        }       

        $rowspanClass = $extra['rowspanClass'];
        
        $html = "<div id='htmlTable'> <table  class='table' >\n";

        if ($extra) {
            $html.= "<tr>";
            for ($i=0; $i < count($head); $i++) { 
                $html.= '<th>'.$head[$i].'</th>';
            }
            $html.= "</tr>";
        }else{
            $html.= $header;
        }
            

        for($i=0; $i<$rows;$i++) {
            $html .= "<tr>\n";
                for($j=0; $j<$cols;$j++) {
                    if ($dataPrint[$i][$j]) {
                        $item = $data[$i][$j] ;
                        if ($dataRow[$i][$j]>1) {
                            $rowspan = $dataRow[$i][$j];
                             $html .= "<td rowspan='$rowspan' class='$rowspanClass'>$item</td>\n";
                        } else {
                            $html .= "<td>$item</td>\n";
                        }
                    }                   
                }
            $html .= "</tr>\n";
        }

        $html .= "</table></div>\n";
        return $html;
    }
	    

     function crearTablaSpans($originalData, $extra) {

        $rec = array();
        $cols = count($originalData[0]);
        $rows = count($originalData); 
        $dataCols = $originalData[0];
        $data = array();

        $dataPrint = array();
        $dataRow = array();

        // Generamos arreglos, uno para impresion, el 2do para rowspans
        for($i=0; $i<$rows;$i++) {
            for($j=0; $j<$cols;$j++) {       
                $dataPrint[$i][$j] = true;
                $dataRow[$i][$j] = 1;
            }

            // Conversion a indice numerico
            $j = 0;
            foreach($dataCols as $key => $itemValue) {            
                $data[$i][$j] = $originalData[$i][$key];
                $j++;
            }
        }

        for($i=0; $i<$rows;$i++) {         
                for($j=0; $j<$cols;$j++) {

                    // Solo prueba en caso de que no haya sido descartado aun el valor
                    if ($dataPrint[$i][$j]) {
                        
                        $testItem = $data[$i][$j];

                        for ($x = $i+1; $x<$rows; $x++) {
                            if ($testItem == $data[$x][$j]) {                                    

                                $dataPrint[$x][$j] = false; // No imprime el valor "consecuente"
                                $dataRow[$i][$j]++;         // Aumenta los rowspans del valor inicial

                            } else {
                                break;
                            }  
                        }
                    }

                }           
        }       

        $rowspanClass = $extra['rowspanClass'];
        
        $html = "<div id='htmlTable'> <table  class='table' >\n";
        $html.= $extra;

        for($i=0; $i<$rows;$i++) {
            $html .= "<tr>\n";

                for($j=0; $j<$cols;$j++) {
                    
                    if ($dataPrint[$i][$j]) {
                        $item = $data[$i][$j] ;
                        if ($dataRow[$i][$j]>1) {
                            $rowspan = $dataRow[$i][$j];
                             $html .= "<td rowspan='$rowspan' class='$rowspanClass'>$item</td>\n";

                        } else {
                            $html .= "<td>$item</td>\n";
                        }
                    }                   
                }
            $html .= "</tr>\n";
        }

        $html .= "</table></div>\n";
        return $html;
    }


	function get_client_ip_env() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	        $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
	}

	function get_client_ip_server() {
	    $ipaddress = '';
	    if ($_SERVER['HTTP_CLIENT_IP'])
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_X_FORWARDED'])
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if($_SERVER['HTTP_FORWARDED_FOR'])
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_FORWARDED'])
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if($_SERVER['REMOTE_ADDR'])
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
	}


	function logFilename($respuesta, $wat) {
	
			$fileName = "log/" . $wat . "_" . date("Ymd_His") . ".txt";					
			$file = fopen($fileName,"w+");
			fwrite($file, $respuesta);
			fclose($file);
			return $fileName;
	}
	
	function logReply($respuesta, $wat) {
	
			$fileName = "log/" . $wat . "_" . date("Ymd_His") . ".txt";					
			$file = fopen($fileName,"w+");
			fwrite($file, $respuesta);
			fclose($file);
	}
	
	function logAppend($respuesta, $fileName) {						
			$file = fopen($fileName,"w+");
			fwrite($file, $respuesta);
			fclose($file);
	}
	
	function guardarHTML($data, $html) {
			$path = generarRuta($data) . ".html";
			$file = fopen($path,"w+");
			fwrite($file, $html);
			fclose($file);
	}
	
	
	function crearRuta($dir) {
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);         
		}
	}
	
	
	function generarRuta($data) {
	
		$criterio = str_replace(" ", "_", $data['criterio']); 
		$year = substr ( $data['fechaConsulta'] , 0, 4);
		$month = substr ( $data['fechaConsulta'] , 5, 2);
		$day = substr ( $data['fechaConsulta'] , 8, 2);
		
		$posFijo = $year . pathSlice . $month . pathSlice . $day . pathSlice;
		
		crearRuta( fileSavePath . $posFijo); //Crear ruta
		
		return  fileSavePath . $posFijo . $year . $month . $day . '_' . limpiezaAcentos($criterio) . '_' . $data['codigoPais'];
	
	}
	
	function arrayDiffEmulation($arrayFrom, $arrayAgainst)
    {
        $arrayAgainst = array_flip($arrayAgainst);
        
        foreach ($arrayFrom as $key => $value) {
            if(isset($arrayAgainst[$value])) {
                unset($arrayFrom[$key]);
            }
        }
        
        return $arrayFrom;
    }

	
	function limpiezaAcentos($str) {
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', '?', 'a', '?', 'e', '?', '?', 'O', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?');
  return str_replace($a, $b, $str);
  }
  
  
  
  function fetchHTML($fileName) {
  
		$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
		$urlPath =  $baseUrl . '/html/'  .  $fileName . '.php';
		//$html = '<strong>HTML!</strong>';
		$html = '<hr />';
		$html .= file_get_contents($urlPath, FILE_USE_INCLUDE_PATH);		
		return $html;
  }
  
  function fetchHTMLStatic($dataArray, $_html500, $_html404) {
  
		if (count($dataArray)> 0) {
			
			$testPath = $dataArray[0]['ruta'];
			$reqLogin = $dataArray[0]['requiereLogin'];
			
			if (file_exists($testPath)) {
		
				if ($reqLogin == "1" && !isset($_SESSION[_prefix . '_idUsuario'])) {						
					$fichero = file_get_contents($_html500, FILE_USE_INCLUDE_PATH);
				} else {
					$fichero = file_get_contents($testPath, FILE_USE_INCLUDE_PATH);
				}
			
			} else {
				$fichero = file_get_contents($testPath, FILE_USE_INCLUDE_PATH);
			}
				
		} else {
			$fichero = file_get_contents($_html404, FILE_USE_INCLUDE_PATH);
		}
		
		return $fichero;
  
  
  }



?>