Currently AfrikaBurns main server runs on ubuntu, this is a detailed install guide so you can match our exact setup.

```
sudo apt-get update
sudo apt-get upgrade -y

sudo apt-get install -y php7.2 php7.2-curl php7.2-gd php7.2-mysql php7.2-bz2 php7.2-zip php7.2-mbstring php7.2-tidy php7.2-opcache php7.2-xml php-uploadprogress php7.2-fpm php7.2-imap php7.2-ldap php7.2-xsl

sudo apt-get install curl php-cli unzip

sudo apt-get install -y mariadb-client
sudo apt-get install -y mariadb-server
sudo apt-get install -y apache2

sudo apt-get -y install php7.2 libapache2-mod-php7.2

sudo apt install php7.2-gmp
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php7.2-fpm

sudo apachectl stop
sudo a2enmod proxy_fcgi setenvif

sudo a2dismod php7.2 
   # This disables mod_php.
sudo a2dismod mpm_itk
sudo a2dismod mpm_prefork 
   # This disables the prefork MPM. Only one MPM can run at a time.
sudo a2enmod mpm_event
   # Enable event MPM. You could also enable mpm_worker.

   #added the following text to the end of
sudo vim /etc/apache2/apache2.conf
Protocols h2 h2c http/1.1

sudo a2enconf php7.2-fpm 



sudo systemctl restart apache2

```
