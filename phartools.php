<?php

/*
 * PharTools (v1.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 01/05/2015 05:45 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/PharTools/blob/master/LICENSE)
 */
 
error_reporting(E_NOTICE); //Comment this line to enable error reporting

echo "EvolSoft PharTools v1.0\n";
if(version_compare(phpversion(), "5.3.0", "<")){
	echo "ERROR: PharTools requires PHP version >= 5.3.0\n";
	exit(1);
}
if(!Phar::canWrite()) echo "WARNING: Phar creation is disabled in php.ini config. Please enable it to create Phar archives\n";
echo "\n";
if(isset($argv[1])){
	if(strtolower($argv[1]) == "-c"){ //PharTools Create command
		if(Phar::canWrite()){
			if(isset($argv[2]) && isset($argv[3])){
				//Get fixed filename
				if(strtolower(substr($argv[3], -5)) == ".phar"){
					$file = $argv[3];
				}else{
					$file = $argv[3] . ".phar";
				}
				$params = array_slice($argv, 4);
				$stub = null;
				$regex = null;
				$metadata = null;
				$compression = null;
				for($i = 0; $i <= count($params) - 1; $i++){
					if($params[$i] == "-c"){ //Check Compression
						if(substr($params[$i + 1], 0, 1) != "-"){
							if(strtolower($params[$i + 1]) == "gzip" || strtolower($params[$i + 1]) == "gz"){
								$compression = Phar::GZ;
							}elseif(strtolower($params[$i + 1]) == "bzip2" || strtolower($params[$i + 1]) == "bz2"){
								$compression = Phar::BZ2;
							}else{
								$compression = null;
							}
						}else{
							echo "Invalid compression specified!\n";
						}

					}elseif($params[$i] == "-m"){ //Check Metadata
						if(substr($params[$i + 1], 0, 1) != "-"){
							$metadata = stringToMetadata($params[$i + 1]);
						}else{
							echo "Invalid metadata specified!\n";
						}
					}elseif($params[$i] == "-s"){ //Check Stub
						if(substr($params[$i + 1], 0, 1) != "-"){
							$stub = $params[$i + 1];
						}else{
							echo "Invalid stub specified!\n";
						}
					}elseif($params[$i] == "-r"){ //Check Regex
						if(substr($params[$i + 1], 0, 1) != "-"){
							$regex = $params[$i + 1];
						}else{
							echo "Invalid regular expression specified!\n";
						}
					}else{
						if($i == count($params)){
							if(substr($params[$i + 1], 0, 1) == "-"){ //Check if is a option
								echo "\"" . $params[$i + 1] . "\" option not recognized\n";
							}
						}else{
							if(substr($params[$i], 0, 1) == "-"){ //Check if is a option
								echo "\"" . $params[$i] . "\" option not recognized\n";
							}
						}
					}
				}
				if(file_exists($argv[2])){
					if(file_exists($file)){
						@unlink($file);
						echo "Overwritting phar...";
					}else{
						echo "Creating phar...";
					}
					$phar = new Phar(getcwd() . "/" . $file, 0, $file);
					//Check compression
					if($compression != null){
						if($compression == Phar::GZ){
							if($phar->canCompress(Phar::GZ)){
								$phar->compress($compression);
								echo "\nCompression set to GZIP";
							}else{
								echo "\nCan't use GZIP compression";
							}
						}elseif($compression == Phar::BZ2){
							if($phar->canCompress(Phar::BZ2)){
								$phar->compress($compression);
								echo "\nCompression set to BZIP2";
							}else{
								echo "\nCan't use BZIP2 compression";
							}
						}
					}
					//Check metadata
					if($metadata != null){
						$phar->setMetadata($metadata);
					}
					//Check stub
					if($stub != null){
						$phar->setStub($stub);
					}
					//Create Phar
					if(is_dir($argv[2])){
						//Check regex
						if($regex != null){
							if(isRegexValid($regex)){
								$phar->buildFromDirectory(getcwd() . "/" . $argv[2], $regex);
							}else{
								echo "\nInvalid regular expression specified!";
								$phar->buildFromDirectory(getcwd() . "/" . $argv[2]);
							}
						}else{
							$phar->buildFromDirectory(getcwd() . "/" . $argv[2]);
						}
						echo "\nPhar file created in " . getcwd() . "!\n";
					}else{
						if(file_exists($argv[2])){
							$phar->addFile($argv[2]);
							echo "\nPhar file created in " . getcwd() . "!\n";
						}else{
							echo "\nSource not found error\n";
						}
					}
				}else{
					echo "\nSource not found error\n";
				}
			}else{
				echo "Usage: -c <source_path|source_file> <destination_phar> [options]\n";
				echo "\nOptions:\n\n";
				echo "-c gzip|bzip2 Compress the phar file using gzip or bzip2 compression\n";
				echo "-m <metadata> Add metadata to the phar file (metadata format must be like 'key=>value,key2=>value2'\n";
				echo "-s <stub> Set stub string for the phar\n";
				echo "-r <regex> Include only files matching the regular expression\n";
			}
		}else{
			echo "Phar creation is disabled in php.ini config. Please enable it to create Phar archives\n";
		}
	}elseif(strtolower($argv[1]) == "-e"){ //PharTools Extract command
		if(isset($argv[2])){
			//Get fixed filename
			if(strtolower(substr($argv[2], -5)) == ".phar" || strpos($argv[2],'.') !== false){
				$file = $argv[2];
			}else{
				$file = $argv[2] . ".phar";
			}
			if(file_exists($file)){
				try{
					$phar = new Phar($file, 0);
					if(isset($argv[3])){
						if(is_dir($argv[3])){
							echo "Extracting...";
							$phar->extractTo($argv[3], null, true);
							echo "\nExtracted in " . $argv[3] . "!\n";
						}else{
							$dir = @mkdir($argv[3]);
							//Check directory
							if($dir){
								echo "Extracting...";
								$phar->extractTo($argv[3], null, true);
								echo "\nExtracted in " . $argv[3] . "!\n";
							}else{
								echo "I/O Error\n";
							}
						}
					}else{
						echo "Extracting...";
						$phar->extractTo(getcwd(), null, true);
						echo "\nExtracted in " . getcwd() . "!\n";
					}
				}catch(Exception $e){
					echo "Invalid phar file\n";
				}
			}else{
				echo "File not found\n";
			}
		}else{
			echo "Usage: -e <phar_file> [extract_directory]\n";
		}
	}elseif(strtolower($argv[1]) == "-i"){ //PharTools Info command
		if(isset($argv[2])){
			//Get fixed filename
			if(strtolower(substr($argv[2], -5)) == ".phar" || strpos($argv[2],'.') !== false){
				$file = $argv[2];
			}else{
				$file = $argv[2] . ".phar";
			}
			if(file_exists($file)){
				try{
					$phar = new Phar($file, 0);
					if($phar->hasMetadata()){
						$metadata = metadataToString($phar->getMetadata());
					}else{
						$metadata = "No metadata found\n";
					}
					echo "Size: " . round((filesize($file) * .0009765625) * .0009765625, 2) ." MB (" . round(filesize($file) * .0009765625, 3) . " KB)\n";
					echo "Signature: " . $phar->getSignature()["hash"] . "\n";
					echo "Signature type: " . $phar->getSignature()["hash_type"] . "\n";
					echo "Writable: " . strbool($phar->isWritable()) . "\n";
					echo "Readable: " . strbool($phar->isReadable()) . "\n";
					echo "Metadata: " . $metadata;
					echo "Show stub (y, n)? ";
					$input = fopen("php://stdin","r");
					$line = fgets($input);
					if(trim($line) == 'y'){
						echo $phar->getStub();
					}
					echo "\n";
				}catch(Exception $e){
					echo "Invalid phar file\n";
				}
			}else{
				echo "File not found\n";
			}
		}else{
			echo "Usage: -i <phar_file>\n";
		}
	}elseif(strtolower($argv[1]) == "-v"){ //PharTools Version command
		echo "EvolSoft website: http://www.evolsoft.tk\n";
		echo "PharTools website: http://phartools.evolsoft.tk\n";
		echo "GitHub: https://github.com/EvolSoft/PharTools\n";
	}else{
		printDefault();
	}
}else{
	printDefault();
}

function printDefault(){
	echo "Usage:\n";
	echo "-c <source_path|source_file> <destination_phar> [options] Creates a phar file\n";
	echo "-e <phar_file> [extract_path] Extracts a phar file\n";
	echo "-i <phar_file> Shows info about a phar file\n";
	echo "-v Get PharTools version\n";
}

function strbool($bool){
	return ($bool) ? 'true' : 'false';
} 

function metadataToString(array $metadata){
	$data = "\n";
	foreach($metadata as $k => $m){
		$data .= "  " . $k . ": " . $m . "\n";
	}
	return $data;
}

function stringToMetadata($string){
	$string = explode(",", $string);
	foreach($string as $v){
		$tmp = explode("=>", $v);
		$data[$tmp[0]] = $tmp[1];
	}
	return $data;
}

function isRegexValid($exp){
	if(@preg_match($exp, "") === false) {
		return false;
	}else{
		return true;
	}
}
?>
