![start2](https://cloud.githubusercontent.com/assets/10303538/6315586/9463fa5c-ba06-11e4-8f30-ce7d8219c27d.png)

## PharTools

**PharTools** is a powerful PHP-CLI tool to manage phar (PHP-Archive) files. It allows to create, extract, edit and view phar archives. It also includes a simple API to implement PharTools features on your own scripts.

***Features:***
- *Create* phar archives
- *Extract* phar archives
- *Add, rename, delete, list* files inside phar archives
- *Get informations* about phar archives (such as filesize, metadata, stub, etc...)
- *Converts* zip or tar archives to phar archives and vice versa
- *Supports* GZip and BZip2 compression types (BZip2 needs the php_bz2 extension which is already included on the Windows installer)

***Don't run this script directly. Run it from a cmd.exe or from a Linux Terminal instance instead***

***If you install PharTools on Windows directories (like Program Files, Program Files (x86), ...) you may need to run cmd.exe as Administrator***

***To create phar files you need to set php.readonly to 0 in php.ini configuration***

## Documentation

### Manual configuration

#### Windows

To configure PharTools on Windows you need only to edit *%PHP_PATH%* in *phartools.cmd* file by setting the right php executable path.

#### Linux

On Linux, PharTools usually doesn't require to be configured but if you have some problems, edit *phartools.sh* file.

### Commands

If you are running PharTools on Windows you can simply run it calling *phartools.cmd* on command shell. On Linux you have to run phartools using *./phartools.sh* instead.

###### Example

*phartools -h* (Windows)

*./phartools.sh -h* (Linux)

#### Add a file to a phar archive:

*phartools -a <phar_archive> <file>*

*file* can be either a file or a non-empty directory.

#### Create a phar archive:

*phartools -c <destination_phar> <source_dir | source_file> [options]*

*options* switches:

-zgzip|-zbzip2 Compress the phar file using gzip or bzip2 compression
-m<metadata> Add metadata to the phar file (metadata format must be like 'key=>value,key2=>value2')
-s<stub> Set stub string for the phar
-r<regex> Include only files matching the regular expression

#### Delete a file from a phar archive:

*phartools -d <phar_archive> <file>*

*file* can be either a file or a directory.

#### Extract a phar archive:

*phartools -e <phar_archive> [extract_path]*

If *extract_path* is not specified, the archive will be extracted in the current directory

#### Get informations of a phar archive:

*phartools -i <phar_archive>*

#### List files inside a phar archive:

*phartools -l <phar_archive>*

#### Rename a file into a phar archive:

*phartools -r <phar_archive> <oldname> <newname>*

#### Convert zip or tar archive to a phar archive:

*phartools -a2p <archive> [compression]*

Currently supported *compression* types are gzip and bzip2


#### Convert a phar archive to a zip or tar archive:

*phartools -p2a <phar_archive> [options]*

*options* switches:
-zgzip|-zbzip2 Set output compression type
-ozip|-otar Set output archive type

## Available PharTools Downloads

**All platforms:**

PharTools_v2.0.zip (Script only)

**Windows:**

PharTools_v2.0_win_installer (Preconfigured Script + Precompiled PHP binaries)
PharTools_v2.0_win_portable (Preconfigured Script + Precompiled PHP binaries, No Installer)

***Please note that precompiled PHP binaries provided in the Portable and Installer versions are provided with minimal extensions and configuration***

## Screenshots

#### Windows

*1. Open Windows Command Shell*<br>
![1](https://cloud.githubusercontent.com/assets/10297075/7434716/2d8a500c-f03d-11e4-84c2-9ef8ab6fee5d.png)<br>
*2. Go to PharTools directory*<br>
![2](https://cloud.githubusercontent.com/assets/10297075/7434729/61c77962-f03d-11e4-89cb-a78ba782f9be.png)<br>
*3. Run PharTools*<br>
![3](https://cloud.githubusercontent.com/assets/10297075/7434743/7b4bbf06-f03d-11e4-83df-493a92ac7075.png)<br>

#### Linux

*1. Open Linux Terminal*<br>
![1 2](https://cloud.githubusercontent.com/assets/10297075/7435028/12525408-f040-11e4-8cf5-94f6e1a18bce.png)<br>
*2. Go to PharTools directory*<br>
![2 2](https://cloud.githubusercontent.com/assets/10297075/7435029/12544d12-f040-11e4-9e9b-e6c44740926f.png)<br>
*3. Run PharTools*<br>
![3 2](https://cloud.githubusercontent.com/assets/10297075/7435030/126e09dc-f040-11e4-84f2-d4c19d9ee5ae.png)<br>

## Donate

If you want you can support this project with a small donation by clicking [:dollar: here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=flavius.c.1999@gmail.com&lc=US&item_name=www.evolsoft.tk&no_note=0&cn=&curency_code=EUR&bn=PP-DonationsBF:btn_donateCC_LG.gif:NonHosted). 
Your generosity will help us paying web hosting, domains, buying programs (such as IDEs, debuggers, etc...) and new hardware to improve software development. Thank you :smile:

## Contributing

If you want to contribute to this project please follow the [Contribution Guidelines](https://github.com/EvolSoft/PharTools/blob/master/CONTRIBUTING.md)


