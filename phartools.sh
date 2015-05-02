#!/bin/bash          
if type php >/dev/null 2>&1; then #Check if PHP is installed
    php phartools.php $@
else
    while true; do
        read -p "PHP is not installed! Do you want to install it now (y, n)? " yn
        case $yn in
            [Yy]* ) sudo apt-get install php5-cli; sudo sed -i.bak 's/^;phar.readonly/phar.readonly/' /etc/php5/cli/php.ini; sudo sed -i.bak 's/phar.readonly = On/phar.readonly = 0/' /etc/php5/cli/php.ini; exit;; #based on default php.ini config (by default ;phar.readonly = 0 or phar.readonly = On)
            [Nn]* ) echo "PHP installation cancelled"; exit;;
            * ) echo "Please enter a correct answer";;
        esac
    done
fi
