![start2](https://cloud.githubusercontent.com/assets/10303538/6315586/9463fa5c-ba06-11e4-8f30-ce7d8219c27d.png)

# PharTools

**PharTools** is a powerful PHP command-line tool to manage phar (PHP-Archive) files. It allows to create, extract, edit and view phar archives. It also includes a simple API to implement PharTools features on your own scripts.

***Features:***
- *Create* phar archives
- *Extract* phar archives
- *Add, rename, delete, list* files inside phar archives
- *Get informations* about phar archives (such as filesize, metadata, stub, etc...)
- *Converts* zip or tar archives to phar archives
- *Converts* phar archives to zip or tar archives
- *Supports* GZip and BZip2 compression types (BZip2 needs the php_bz2 extension which is already included on the Windows installer)

***NOTE: to create phar files you need to set php.readonly to 0 in php.ini configuration***

## Requirements

- At least PHP 5.3.0
- *Optional:* php_bz2 extension (to implement BZip2 compression support)

## Installation

You can download the PharTools packages at [this page](https://www.evolsoft.tk/phartools/download/). You will find three download options:

**All platforms:**

PharTools_vx.x.zip (script only)

**Windows:**

PharTools_vx.x_win_installer.exe (preconfigured script + precompiled PHP binaries)<br>
PharTools_vx.x_win_portable.zip (preconfigured script + precompiled PHP binaries, no installer)

***Please note that precompiled PHP binaries included in the portable and installer pacakages are provided with minimal extensions and configuration***

### Windows

PharTools installation on Windows is very simple. You have three choices:
- download the installer
- download the Windows portable package
- download the all-platforms zip package and configure PharTools manually

If choose the third option, you must simply edit the `%PHP_PATH%` variable in `phartools.cmd` file by setting a valid PHP executables path.

***NOTE: if you install PharTools on Windows directories (like Program Files, Program Files (x86), ...) you may need administrator privileges in order to run the script correctly.***

### Linux

To install PharTools on Linux, download the all-platforms zip package. Then run:

```
$ ./phartools.sh
```

It should automatically install the PHP package (if missing) in order to run PharTools correctly or run PharTools directly.
If you have problems while installing the PHP package, try to install it manually from your Linux distro package manager (i.e. *apt-get*).

### macOS

You can run PharTools also on macOS. PharTools installation on macOS is very easy: you just need to download the all-platforms zip package. No PHP installation is required because PHP is already bundled with macOS since Mac OS X 10.0.0.

## Usage

```
$ phartools -h

Usage:
  -a <phar_archive> <files> Add files to a phar archive
  -c <phar_archive> <files> [options] Create a phar archive
  -d <phar_archive> <file> Delete a file from a phar archive
  -e <phar_archive> [extract_path] Extract a phar archive
  -h Show this help screen
  -i <phar_archive> Show info about a phar archive
  -l <phar_archive> List the content of a phar archive
  -r <phar_archive> <oldname> <newname> Rename a file inside a phar archive
  -a2p <archive> [compression] Convert a zip or tar archive to a phar archive
  -p2a <phar_archive> [options] Convert a phar archive to a zip or tar archive
```

### -a (add) command

Adds files to a phar archive.

```
$ phartools -a <phar_archive> <files>
```

Parameters:

```
<phar_archive> is the destination phar archive
<files> are the files you want to add (wildcards are allowed)
```

### -c (create) command

Creates a phar archive.

```
$ phartools -c <phar_archive> <files> [options]
```

Parameters:

```
<phar_archive> is the name of the resulting phar archive
<files> are the source files to add inside the phar archive (wildcards are allowed)
[options] are optional switches:
	-zgzip|-zbzip2 Compress the phar file using gzip or bzip2 compression
	-m<metadata> Add metadata to the phar file (metadata format must be like 'key=>value,key2=>value2')
	-s<stub> Set stub string for the phar
	-r<regex> Include only files matching the regular expression
```

### -d (delete) command

Deletes a file from a phar archive.

```
$ phartools -d <phar_archive> <file>
```

Parameters:

```
<phar_archive> is the destination phar archive
<file> is the file or the directory you want to delete
```

### -e (extract) command

Extracts a phar archive.

```
$ phartools -e <phar_archive> [extract_path]
```

Parameters:

```
<phar_archive> is the destination phar archive
[extract_path] is an optional parameter specifying the path on which the phar archive contents will be extracted
```

### -i (show archive info) command

Shows informations about a phar archive.

```
$ phartools -i <phar_archive>
```

Parameters:

```
<phar_archive> is the destination phar archive
```

### -l (list archive content) command

Lists the content of a phar archive.

```
$ phartools -l <phar_archive>
```

Parameters:

```
<phar_archive> is the destination phar archive
```

### -r (rename) command

Renames a file inside a phar archive.

```
$ phartools -r <phar_archive> <oldname> <newname>
```

Parameters:

```
<phar_archive> is the destination phar archive
<oldname> is the name of the file to rename
<newfile> is the new filename
```

### -a2p (archive to phar archive) command

Converts a zip or tar archive to a phar archive.

```
$ phartools -a2p <archive> [compression]
```

Parameters:

```
<archive> is the zip or tar archive to convert
[compression] is an optional parameter specifying compression:
	gzip Compress the phar archive using gzip compression
	bzip2 Compress the phar archive using bzip2 compression
```

### -p2a (phar archive to archive) command

Converts a phar archive to a zip or tar archive.

```
$ phartools -p2a <phar_archive> [options]
```

Parameters:

```
<phar_archive> is the destination phar archive
[options] are optional switches:
	-zgzip|-zbzip2 Compress the resulting archive file using gzip or bzip2 compression (zip archives do not support compression)
	-ozip|-otar Set the output archive format (zip or tar archive)
```

## API

PharTools provides also an API to simplify the usage of phar archives:

```php
include "path/to/phartools.php";

PharTools::<api_function>();
```

## Screenshots

### Windows

*PharTools command-line interface*<br>
![1](https://user-images.githubusercontent.com/10297075/41868510-9143a706-78b6-11e8-859c-b82aaba932ae.png)<br>
*PharTools file list example*<br>
![2](https://user-images.githubusercontent.com/10297075/41868515-92b85a64-78b6-11e8-9113-74c88bc781dd.png)<br>

### Linux

*PharTools command-line interface*<br>
![3](https://user-images.githubusercontent.com/10297075/41877166-9814296a-78d0-11e8-9747-dee47fba20b8.png)<br>
*PharTools file list example*<br>
![4](https://user-images.githubusercontent.com/10297075/41877170-9979bc52-78d0-11e8-9ec1-1605cecae28c.png)<br>

### macOS

*PharTools command-line interface*<br>
![5](https://user-images.githubusercontent.com/10297075/41915696-a0ade742-7956-11e8-9efe-e407cd9e2f08.png)<br>
*PharTools file list example*<br>
![6](https://user-images.githubusercontent.com/10297075/41915697-a0d47164-7956-11e8-9457-8969c52076fb.png)<br>

## Donate

If you want you can support this project with a small donation by clicking [:dollar: here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=flavius.c.1999@gmail.com&lc=US&item_name=www.evolsoft.tk&no_note=0&cn=&curency_code=EUR&bn=PP-DonationsBF:btn_donateCC_LG.gif:NonHosted). 
Your generosity will help us paying web hosting, domains, buying programs (such as IDEs, debuggers, etc...) and new hardware to improve software development. Thank you :smile:

## Contributing

If you want to contribute to this project please follow the [Contribution Guidelines](https://github.com/EvolSoft/PharTools/blob/master/CONTRIBUTING.md).