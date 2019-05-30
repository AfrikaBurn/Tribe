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

# Setting up apache links
Create a new apache virtual host
```
cd /etc/apache2/sites-available/
sudo vim tribe.conf
```

You will need to added the following details to the folder. If you plan to install Tribe in a separate location, please ensure you use the correct folder location. 
```
<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        #ServerName www.example.com


        ServerName tribe.localhost
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/tribe/web

        DirectoryIndex index.php

        DocumentRoot /var/www/tribe/web
        <Directory /var/www/tribe/web>
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>


        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        LogLevel warn
        
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        # For most configuration files from conf-available/, which are
        # enabled or disabled at a global level, it is possible to
        # include a line for only one particular virtual host. For example the
        # following line enables the CGI configuration for this host only
        # after it has been globally disabled with "a2disconf".
        #Include conf-available/serve-cgi-bin.conf
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
```

Enable the configuration 
```
sudo a2ensite tribe.conf
```

You will need to setup a redirect on your local so your browser will open your virtual host.
```
sudo vim /etc/hosts
```

add the following line
```
127.0.1.1       tribe.localhost
```
