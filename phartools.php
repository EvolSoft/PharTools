<?php

/*
 * PharTools v2.1 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/PharTools/blob/master/LICENSE)
 */

error_reporting(E_NOTICE); //Comment this line to enable error reporting

/**
 * PharTools main class
 * 
 * @author Flavius12
 * 
 * @package PharTools
 */
class PharTools {
    
    /** @var int */
    const ERROR = 0;
    
    /** @var int */
    const SUCCESS = 1;
    
    /** @var int */
    const ERR_INV_COMP = 2;
    
    /** @var int */
    const ERR_META = 3;
    
    /** @var int */
    const ERR_STUB = 4;
    
    /** @var int */
    const ERR_RDONLY = 5;
    
    /** @var int */
    const ERR_FILE_NOT_FOUND = 6;
    
    /** @var int */
    const ERR_ARCH_NOT_FOUND = 7;
    
    /** @var int */
    const ERR_INV_ARCH = 8;
    
    /** @var int */
    const ERR_LAST_FILE = 9;
    
    /**
     * Create a phar archive
     * 
     * @param string $fname
     * @param array $files
     * @param int $comp
     * @param array $meta
     * @param string $stub
     * 
     * @return int
     */
    public static function Create($arch, $files, $comp = null, array $meta = null, $stub = null){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        if(file_exists($arch)){
            if(!@unlink($arch)){
                return self::ERR_DELETE_ARCH;
            }
        }
        $phar = new Phar($arch);
        $status = self::_AddFile($phar, $files);
        if($status != self::SUCCESS) return $status;
        if($comp){
            $status = self::SetCompression($phar, $comp);
            if($status != self::SUCCESS) return $status;
        }
        if($meta){
            $status = self::SetMetadata($phar, $meta);
            if($status != self::SUCCESS) return $status;
        }
        if($stub){
            $status = self::SetStub($phar, $stub);
            if($status != self::SUCCESS) return $status;
        }
        return self::SUCCESS;
    }
    
    /**
     * Open a phar archive
     * 
     * @param string $arch
     * @param int $status
     * 
     * @return Phar
     */
    public static function Open($arch, &$status){
        if(!file_exists($arch) || is_dir($arch)){
            $status = self::ERR_ARCH_NOT_FOUND;
            return null;
        }
        try{
            $phar = new Phar($arch);
            $status = self::SUCCESS;
            return $phar;
        }catch(UnexpectedValueException $ex){
            $status = self::ERR_INV_ARCH;
            return null;
        }
    }
    
    /**
     * Set phar archive compression
     *
     * @param Phar $phar
     * @param int $type
     *
     * @return int
     */
    public static function SetCompression(Phar &$phar, $type){
        if(Phar::canCompress($type)){
            $phar = $phar->compress($type);
            return self::SUCCESS;
        }
        return self::ERR_INV_COMP;
    }
    
    /**
     * Set phar archive metadata
     *
     * @param Phar $phar
     * @param array $metadata
     *
     * @return int
     */
    public static function SetMetadata(Phar $phar, $metadata){
        try{
            $phar->setMetadata($metadata);
            return self::SUCCESS;
        }catch(PharException $ex){
            return self::ERR_META;
        }
    }
    
    /**
     * Set phar archive bootstrap stub
     *
     * @param Phar $phar
     * @param string $stub
     *
     * @return int
     */
    public static function SetStub(Phar $phar, $stub){
        try{
            $phar->setStub($stub);
            return self::SUCCESS;
        }catch(PharException $ex){
            return self::ERR_STUB;
        }
    }
    
    /**
     * Make directory inside the phar archive
     *
     * @param string $arch
     * @param string $dir
     *
     * @return int
     */
    public static function MkDir($arch, $dir){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        return self::_MkDir($phar, $dir);
    }
    
    /**
     * Make a directory inside the phar archive
     *
     * @param Phar $phar
     * @param string $dir
     *
     * @return int
     */
    public static function _MkDir(Phar $phar, $dir){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        try{
            $phar->addEmptyDir($dir);
            return self::SUCCESS;
        }catch(PharException $ex){
            return self::ERROR;
        }
    }
    
    /**
     * Add file(s) into the phar archive
     *
     * @param string $arch
     * @param string $file
     * @param callable $callback
     * 
     * @return int
     */
    public static function AddFile($arch, $file){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        return self::_AddFile($phar, $file);
    }
    
    /**
     * Add file(s) into the phar archive
     *
     * @param Phar $phar
     * @param string $file
     * 
     * @return int
     */
    public static function _AddFile(Phar $phar, $file){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $glob = glob($file);
        if(empty($glob)){
            return self::ERR_FILE_NOT_FOUND;
        }
        foreach($glob as $f){
            if(is_dir($f)){
                $phar->buildFromIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(getcwd() . "/" . $f . "/", FilesystemIterator::SKIP_DOTS)), "./");
            }else{
                $phar->addFile($f);
            }
        }
        return self::SUCCESS;
    }
    
    /**
     * @internal
     *
     * Recursively delete files into phar archive
     *
     * @param string $path
     * @param int $status
     * 
     */
    private static function RecDeleteFiles(Phar $phar, $path, &$status){
        $entries = new DirectoryIterator($path);
        foreach($entries as $entry){
            if($entry->isDir()){
                self::RecDeleteFiles($phar, $entry->getPathname(), $status);
            }else{
                if($status == self::SUCCESS){
                    if($phar->count() == 1){
                        $status = self::ERR_LAST_FILE;
                        return;
                    }
                    $status = unlink($entry->getPathname()) ? self::SUCCESS : self::ERROR;
                }
            }
        }
    }
    
    /**
     * Delete a file inside the phar archive
     *
     * @param string $arch
     * @param string $file
     *
     * @return int
     */
    public static function DeleteFile($arch, $file){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        return self::_DeleteFile($phar, $file);
    }
    
    /**
     * Delete a file inside the phar archive
     *
     * @param Phar $phar
     * @param string $file
     *
     * @return int
     */
    public static function _DeleteFile(Phar $phar, $file){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $path = "phar://" . $phar->getPath() . "/";
        if(!file_exists($path . $file)){
            return self::ERR_FILE_NOT_FOUND;
        }else if(is_dir($path . $file)){
            $status = self::SUCCESS;
            self::RecDeleteFiles($phar, $path . $file . "/", $status);
            return $status;
        }
        try{
            if($phar->count() == 1){
                return self::ERR_LAST_FILE;
            }
            $phar->delete($file);
            return self::SUCCESS;
        }catch(Exception $ex){
            return self::ERROR;
        }
    }
    
    /**
     * Rename file inside the phar archive
     *
     * @param string $arch
     * @param string $file
     * @param string $newname
     *
     * @return int
     */
    public static function RenameFile($arch, $file, $newname){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        return self::_RenameFile($phar, $file, $newname);
    }
    
    /**
     * Rename file inside the phar archive
     *
     * @param Phar $phar
     * @param string $file
     * @param string $newname
     *
     * @return int
     */
    public static function _RenameFile(Phar $phar, $file, $newname){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        if(isset($phar[$file])){
            $phar[$newname] = $phar[$file]->getContent();
            unset($phar[$file]);
            return self::SUCCESS;
        }
        return self::ERR_FILE_NOT_FOUND;
    }
    
    /**
     * Extract a phar archive
     * 
     * @param string $arch
     * @param string|null $dest
     * 
     * @return int
     */
    public static function Extract($arch, &$dest = null){
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        return self::_Extract($phar, $dest);
    }
    
    /**
     * Extract a phar archive
     *
     * @param Phar $phar
     * @param string|null $dest
     *
     * @return int
     */
    public static function _Extract(Phar $phar, &$dest = null){
        if(!$dest){
            $dest = getcwd();
        }else{
            $dest = getcwd() . DIRECTORY_SEPARATOR . $dest;
        }
        if(!is_dir($dest)){
            $dir = @mkdir($dest);
            $dest = realpath($dest);
            if(!$dir){
                return self::ERROR;
            }
        }
        try{
            $phar->extractTo($dest, null, true);
            return self::SUCCESS;
        }catch(PharException $ex){
            return self::ERROR;
        }
    }
    
    /**
     * Convert a zip or tar archive to a phar archive
     *
     * @param string $arch
     * @param int $fmt
     * @param int $comp
     * 
     * @return int
     */
    public static function ToPhar($arch, $fmt, $comp = null){
        if(!Phar::canWrite()) return self::ERR_RDONLY;
        if(!Phar::canCompress($comp)){
            return self::ERR_INV_COMP;
        }
        if(!file_exists($arch) || is_dir($arch)){
            return self::ERR_ARCH_NOT_FOUND;
        }
        try{
            $pdata = new PharData($arch);
            $pdata->convertToExecutable($fmt, $comp);
            return self::SUCCESS;
        }catch(Exception $ex){
            return self::ERROR;
        }
    }
    
    /**
     * Convert the phar archive to a zip or tar archive
     *
     * @param string $arch
     * @param int $fmt
     * @param int $comp
     * 
     * @return int
     */
    public static function ToArchive($arch, $fmt, $comp = null){
        if(!Phar::canCompress($comp)){
            return self::ERR_INV_COMP;
        }
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        try{
            $phar->convertToData($fmt, $comp);
            return self::SUCCESS;
        }catch(Exception $ex){
            return self::ERROR;
        }
    }
    
    /**
     * Get phar archive info
     *
     * @param string $arch
     * @param array $info
     * 
     * @return array
     */
    public static function GetPharInfo($arch, &$info){
        $status = null;
        $phar = self::Open($arch, $status);
        if($status != self::SUCCESS) return $status;
        $info["filename"] = $arch;
        $info["filesize"] = filesize($arch);
        $info["signature"] = $phar->getSignature();
        $info["version"] = $phar->getVersion();
        $info["writable"] = $phar->isWritable();
        $info["readable"] = $phar->isReadable();
        if($phar->hasMetadata()){
            $info["metadata"] = $phar->getMetadata();
        }else{
            $info["metadata"] = null;
        }
        $info["stub"] = $phar->getStub();
        return self::SUCCESS;
    }
    
    /**
     * Check if PHP Phar extension is supported
     *
     * @return bool
     */
    public static function IsPharSupported(){
        return version_compare(phpversion(), "5.3.0", ">=");
    }
    
    /**
     * Get PharTools version
     *
     * @return string
     */
    public static function GetVersion(){
        return "2.1";
    }
}

/**
 * PharTools CLI helper class
 *
 * @author Flavius12
 * 
 * @package PharTools
 */
class PharToolsHelper {
    
    /**
     * Convert boolean to string
     *
     * @param bool $bool
     *
     * @return string
     */
    public static function strbool($bool){
        return ($bool) ? "true" : "false";
    }
    
    /**
     * Convert phar archive metadata to string
     *
     * @param array $metadata
     *
     * @return string
     */
    public static function MetadataToString(array $metadata){
        $data = "\n";
        foreach($metadata as $k => $m){
            $data .= "  \"" . $k . "\" => \"" . $m . "\"\n";
        }
        return $data;
    }
    
    /**
     * Convert string to phar archive metadata
     *
     * @param string $string
     *
     * @return array
     */
    public static function StringToMetadata($string){
        $string = explode(",", $string);
        foreach($string as $v){
            $tmp = explode("=>", $v);
            $data[$tmp[0]] = $tmp[1];
        }
        return $data;
    }
    
    /**
     * Format size
     *
     * @param float $bytes
     *
     * @return string
     */
    public static function FormatSize($bytes){
        if($bytes >= 1073741824){
            $bytes = number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        }else if($bytes >= 1048576){
            $bytes = number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }else if($bytes >= 1024){
            $bytes = number_format($bytes / 1024, 2, ',', '.') . ' KB';
        }else{
            $bytes = number_format($bytes, 0, ',', '.') . ' bytes';
        }
        return $bytes;
    }
    
    /**
     * Append phar archive extension if missing
     *
     * @param string $str
     *
     * @return string
     */
    public static function appendFileExtension($str){
        if(strtolower(substr($str, -5)) != ".phar") $str .= ".phar";
        return $str;
    }
    
    /**
     * Print help screen
     */
    public static function PrintHelp(){
        echo "Usage:\n";
        echo "  -a <phar_archive> <files> Add files to a phar archive\n";
        echo "  -c <phar_archive> <files> [options] Create a phar archive\n";
        echo "  -d <phar_archive> <file> Delete a file from a phar archive\n";
        echo "  -e <phar_archive> [extract_path] Extract a phar archive\n";
        echo "  -h Show this help screen\n";
        echo "  -i <phar_archive> Show info about a phar archive\n";
        echo "  -l <phar_archive> List the content of a phar archive\n";
        echo "  -r <phar_archive> <oldname> <newname> Rename a file inside a phar archive\n";
        echo "  -a2p <archive> [compression] Convert a zip or tar archive to a phar archive\n";
        echo "  -p2a <phar_archive> [options] Convert a phar archive to a zip or tar archive\n";
    }
    
    /**
     * Print invalid command string
     */
    public static function PrintInvalidCommand(){
        echo "Invalid command usage. Run phartools -h or phartools ? to show help.\n";
    }
    
    /**
     * Print phar read-only warning
     */
    public static function PrintReadOnlyWarning(){
        echo "Warning: phar archive creation/modification is disabled in php.ini config. Please enable it to create or modify phar archives.\n\n";
    }
    
    /**
     * Print phar read-only error
     */
    public static function PrintReadOnlyError(){
        echo "Error: phar archive creation/modification is disabled.\n";
    }
    
    /**
     * Add file(s) inside phar
     *
     * @param string $arch
     * @param string $path
     * 
     * @return int
     */
    public static function AddFiles($arch, $path){
        $status = null;
        $phar = PharTools::Open($arch, $status);
        switch($status){
            case PharTools::SUCCESS:
                $entries = glob($path);
                foreach($entries as $entry){
                    $pharpath = "phar://" . $phar->getPath() . "/";
                    if(file_exists($pharpath . $entry)){
                        echo $entry . " already exists in the phar archive. Overwrite it (y, n)? ";
                        $input = fopen("php://stdin","r");
                        $line = strtolower(fgets($input));
                        if(trim($line) != 'y'){
                            goto skip;
                        }
                    }
                    if(PharTools::_AddFile($phar, $entry) == PharTools::SUCCESS){
                        echo (is_dir($entry) ? "Directory " : "File ") . $entry . " added to " . $arch . "\n";
                    }else{
                        echo "Failed to add " . $entry . " to " . $arch . ".\n";
                    }
                    skip:
                }
                return 0;
            case PharTools::ERR_ARCH_NOT_FOUND:
                echo "Phar archive not found.\n";
                return 1;
            case PharTools::ERR_INV_ARCH:
                echo "The specified file is not a valid phar archive.\n";
                return 1;
        }
    }
    
    /**
     * List content inside phar archive
     *
     * @param Phar $phar
     */
    public static function ListContent(Phar $phar){
        static $fcount = 0;
        static $totsize = 0;
        static $sub = 0;
        $sub++;
        $path = "phar://" . $phar->getPath();
        if($sub == 1){
            echo "Listing files of " . pathinfo($phar->getPath(), PATHINFO_BASENAME) . "\n\n";
            echo "Date       Time     Size                 Name\n";
            echo "---------- -------- -------------------- -----------------------\n";
        }
        foreach($phar as $file){
            if(!$file->isDir()){
                printf("%s %20s %s\n", date("d/m/Y H:i:s", $file->getCTime()), number_format($file->getSize(), 0, ',', '.'), substr($file->getPathname(), strlen($path) + 1, strlen($file->getPathname()) - strlen($path) - 1));
                $fcount++;
                $totsize += $file->getSize();
            }
        }
        foreach($phar as $file){
            if($file->isDir()){
                $dir = new Phar($file->getPathname());
                self::ListContent($dir);
            }
        }
        if($sub == 1){
            echo "---------- -------- -------------------- -----------------------\n";
            printf("                    %20s %s file(s)\n", number_format($totsize, 0, ',', '.'), number_format($fcount, 0, ',', '.'));
        }
        $sub--;
    }
}

//If the script is called from command line, execute it
if(isset($argv[0])){
    return main($argc, $argv);
}

function main($argc, $argv){
    echo "\nEvolSoft PharTools v" . PharTools::GetVersion() . "\nCopyright (C) 2015-2018 EvolSoft. Licensed under MIT.\n\n";
    if(!PharTools::IsPharSupported()){
        echo "Error: PharTools requires PHP version >= 5.3.0\n";
        return 1;
    }
    if(!Phar::canWrite()) PharToolsHelper::PrintReadOnlyWarning();
    if(!isset($argv[1])){
        PharToolsHelper::PrintHelp();
        return 0;
    }
    switch(strtolower($argv[1])){
        case "-a":
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc != 4){
                PharToolsHelper::PrintInvalidCommand();
                return 1;
            } 
            if(!Phar::canWrite()){
                PharToolsHelper::PrintReadOnlyError();
                return 1;
            }
            return PharToolsHelper::AddFiles($argv[2], $argv[3]);
        case "-c":
            $comp = null;
            $meta = null;
            $stub = null;
            $ext = null;
            if(!isset($argv[3])){
                echo "Please specify source files.\n";
                return 0;
            }
            for($i = 4; $i < $argc; $i++){
                if(strncasecmp($argv[$i], "-z", 2) == 0){
                    $comp = substr($argv[$i], 2, strlen($argv[$i]) - 2);
                }else if(strncasecmp($argv[$i], "-m", 2) == 0){
                    $meta = substr($argv[$i], 2, strlen($argv[$i]) - 2);
                }else if(strncasecmp($argv[$i], "-s", 2) == 0){
                    $stub = substr($argv[$i], 2, strlen($argv[$i]) - 2);
                }else{
                    PharToolsHelper::PrintInvalidCommand();
                    return 1;
                }
            }
            if($comp){
                switch(strtolower($comp)){
                    case "gzip":
                    case "gz":
                        $ext = ".gz";
                        $comp = Phar::GZ;
                        break;
                    case "bzip2":
                    case "bz2":
                        $ext = ".bz2";
                        $comp = Phar::BZ2;
                        break;
                    default:
                        echo "Invalid compression specified.\n";
                        return 1;
                }
            }
            if($meta){
                $meta = PharToolsHelper::StringToMetadata($meta);
            }
            $arch = PharToolsHelper::appendFileExtension($argv[2]);
            if(file_exists($arch . $ext)){
                echo "Overwrite " . $arch . $ext . " (y, n)? ";
                $input = fopen("php://stdin","r");
                $line = strtolower(fgets($input));
                if(trim($line) != 'y'){
                    echo "Phar archive creation cancelled.\n";
                    return 0;
                }
                echo "Overwriting phar archive...\n";
            }else{
                echo "Creating phar archive...\n";
            }
            switch(PharTools::Create($arch . $ext, $argv[3], $comp, $meta, $stub)){
                case PharTools::SUCCESS:
                    echo "Phar archive " . $arch . $ext . " created successfully.\n";
                    return 0;
                case PharTools::ERR_RDONLY:
                    PharToolsHelper::PrintReadOnlyError();
                    return 1;
                case PharTools::ERR_FILE_NOT_FOUND:
                    echo "Source file(s) not found.\n";
                    return 1;
                case PharTools::ERR_INV_COMP:
                    echo "Unsupported compression type.\n";
                    return 1;
                case PharTools::ERR_META:
                    echo "Invalid metadata specified.\n";
                    return 1;
                case PharTools::ERR_STUB:
                    echo "Invalid stub specified.\n";
                    return 1;
                default:
                case PharTools::ERROR:
                    echo "An error has occurred while creating the phar archive.\n";
                    return 1;
            }
        case "-d":
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc != 4){
                PharToolsHelper::PrintInvalidCommand();
                return 1;
            }
            switch(PharTools::DeleteFile($argv[2], $argv[3])){
                case PharTools::SUCCESS:
                    echo "File " . $argv[3] . " deleted from " . $argv[2] . ".\n";
                    return 0;
                case PharTools::ERR_RDONLY:
                    PharToolsHelper::PrintReadOnlyError();
                    return 1;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
                case PharTools::ERR_LAST_FILE:
                    echo "Failed to delete " . $argv[3] . " from " . $argv[2] . ". The archive cannot be empty.\n";
                    return 1;
                default:
                case PharTools::ERROR:
                    echo "Failed to delete " . $argv[3] . " from " . $argv[2] . ".\n";
                    return 1;
            }
        case "-e":
            $dest = null;
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc > 4){
                PharToolsHelper::PrintInvalidCommand();
                return 0;
            }
            $status = null;
            $phar = PharTools::Open($argv[2], $status);
            switch($status){
                case PharTools::SUCCESS:
                    if(isset($argv[3])){
                        $dest = $argv[3];
                    }
                    echo "Extracting " . $argv[2] . "...\n";
                    switch(PharTools::_Extract($phar, $dest)){
                        case PharTools::SUCCESS:
                            echo "Extracted in " . $dest . "\n";
                            return 0;
                        default:
                        case PharTools::ERROR:
                            echo "An error has occurred while extracting the phar archive.\n";
                            return 1;
                    }
                    return 0;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
            }
        case "-h":
        case "?":
            PharToolsHelper::PrintHelp();
            return 0;
        case "-i":
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc > 3){
                PharToolsHelper::PrintInvalidCommand();
                return 0;
            }
            $info = array();
            switch(PharTools::GetPharInfo($argv[2], $info)){
                case PharTools::SUCCESS:
                    echo "Archive name: " . $argv[2] . "\n";
                    if($info["filesize"] > 1024){
                        echo "Size: " . PharToolsHelper::FormatSize($info["filesize"]) . " (" . number_format($info["filesize"], 0, ',', '.') . " bytes)\n";
                    }else{
                        echo "Size: " . PharToolsHelper::FormatSize($info["filesize"]) . "\n";
                    }
                    echo "Signature: " . $info["signature"]["hash"] . "\n";
                    echo "Signature type: " . $info["signature"]["hash_type"] . "\n";
                    echo "Version: " . $info["version"] . "\n";
                    echo "Writable: " . PharToolsHelper::strbool($info["writable"]) . "\n";
                    echo "Readable: " . PharToolsHelper::strbool($info["readable"]) . "\n";
                    if($info["metadata"]){
                        $metadata = PharToolsHelper::MetadataToString($info["metadata"]);
                    }else{
                        $metadata = "No metadata found.\n";
                    }
                    echo "Metadata: " . $metadata;
                    echo "Show stub (y, n)? ";
                    $input = fopen("php://stdin","r");
                    $line = strtolower(fgets($input));
                    if(trim($line) == 'y'){
                        echo $info["stub"] . "\n";
                    }
                    return 0;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
            }
        case "-l":
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc > 3){
                PharToolsHelper::PrintInvalidCommand();
                return 0;
            }
            $status = null;
            $phar = PharTools::Open($argv[2], $status);
            switch($status){
                case PharTools::SUCCESS:
                    PharToolsHelper::ListContent($phar);
                    break;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
            }
            return 0;
        case "-r":
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }else if($argc != 5){
                PharToolsHelper::PrintInvalidCommand();
                return 0;
            } 
            switch(PharTools::RenameFile($argv[2], $argv[3], $argv[4])){
                case PharTools::SUCCESS:
                    echo $argv[3] . " renamed to " . $argv[4] . " into " . $argv[2] . ".\n";
                    return 0;
                case PharTools::ERR_RDONLY:
                    PharToolsHelper::PrintReadOnlyError();
                    return 1;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
                case PharTools::ERR_FILE_NOT_FOUND:
                    echo "File " . $argv[3] . " not found into the phar archive.\n";
                    return 1;
                default:
                case PharTools::ERROR:
                    echo "An error has occurred while renaming the file.\n";
                    return 1;
            }
        case "-a2p":
            $comp = null;
            if(!isset($argv[2])){
                echo "Please specify an archive file.\n";
                return 0;
            }else if($argc > 4){
                PharToolsHelper::PrintInvalidCommand();
                return 0;
            }
            if(isset($argv[3])){
                switch(strtolower($argv[3])){
                    case "gzip":
                    case "gz":
                        $comp = Phar::GZ;
                        break;
                    case "bzip2":
                    case "bz2":
                        $comp = Phar::BZ2;
                        break;
                    default:
                        echo "Invalid compression specified.\n";
                        return 1;
                }
            }
            switch(PharTools::ToPhar($argv[2], Phar::PHAR, $comp)){
                case PharTools::SUCCESS:
                    echo $argv[2] . " converted to phar archive.\n";
                    return 0;
                case PharTools::ERR_RDONLY:
                    PharToolsHelper::PrintReadOnlyError();
                    return 1;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Archive file not found.\n";
                    return 1;
                case PharTools::ERR_INV_COMP:
                    echo "Unsupported compression type.\n";
                    return 1;
                default:
                case PharTools::ERROR:
                    echo "An error has occurred.\n";
                    return 1;
            }
        case "-p2a":
            $fmt = null;
            $fmtname = "ZIP";
            $comp = null;
            if(!isset($argv[2])){
                echo "Please specify a phar archive.\n";
                return 0;
            }
            for($i = 3; $i < $argc; $i++){
                if(strncasecmp($argv[$i], "-z", 2) == 0){
                    $comp = substr($argv[$i], 2, strlen($argv[$i]) - 2);
                }else if(strncasecmp($argv[$i], "-o", 2) == 0){
                    $fmt = substr($argv[$i], 2, strlen($argv[$i]) - 2);
                }else{
                    PharToolsHelper::PrintInvalidCommand();
                    return 1;
                }
            }
            if($comp){
                switch(strtolower($comp)){
                    case "gzip":
                    case "gz":
                        $comp = Phar::GZ;
                        break;
                    case "bzip2":
                    case "bz2":
                        $comp = Phar::BZ2;
                        break;
                    default:
                        echo "Invalid compression specified.\n";
                        return 1;
                }
            }
            if($fmt){
                switch(strtolower($fmt)){
                    case "zip":
                        $fmt = Phar::ZIP;
                        break;
                    case "tar":
                        $fmtname = "TAR";
                        $fmt = Phar::TAR;
                        break;
                    default:
                        echo "Invalid format specified.\n";
                        return 1;
                }
            }else{
                $fmt = Phar::ZIP; //Default format
            }
            if($fmt == Phar::ZIP && $comp){ //ZIP compression not supported!
                echo "ZIP compression is not supported.\n";
                return 1;
            }
            switch(PharTools::ToArchive($argv[2], $fmt, $comp)){
                case PharTools::SUCCESS:
                    echo $argv[2] . " converted to " . $fmtname . " archive.\n";
                    return 0;
                case PharTools::ERR_ARCH_NOT_FOUND:
                    echo "Phar archive not found.\n";
                    return 1;
                case PharTools::ERR_INV_ARCH:
                    echo "The specified file is not a valid phar archive.\n";
                    return 1;
                case PharTools::ERR_INV_COMP:
                    echo "Unsupported compression type.\n";
                    return 1;
                default:
                case PharTools::ERROR:
                    echo "An error has occurred.\n";
                    return 1;
            }
        default:
            echo "Invalid command \"" . $argv[1] . "\". Run phartools -h or phartools ? to show help.\n";
            return 0;
    }
}
