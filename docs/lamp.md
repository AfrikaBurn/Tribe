Currently AfrikaBurns main server runs on ubuntu, this is a detailed install guide so you can match our exact setup. We normally a few months behind the most current long term stable version.

```
sudo add-apt-repository ppa:ondrej/php

sudo apt update
sudo apt upgrade -y

sudo apt-get install php7.3
sudo a2enmod php7.3

sudo apt-get install -y php7.3 php7.3-bz2 php7.3-cli php7.3-common php7.3-curl php7.3-fpm php7.3-gd php7.3-gmp php7.3-imap php7.3-json php7.3-ldap php7.3-mbstring php7.3-mysql php7.3-opcache php7.3-readline php7.3-tidy php7.3-xml php7.3-zip

sudo apt-get install curl php-cli unzip

sudo apt-get install -y mariadb-client

# We have load balanced servers, so the dabase is on a completely different system. 
# But our local development enviroments use local databases.
sudo apt-get install -y mariadb-server

sudo apt-get -y install php7.3 libapache2-mod-php7.3
sudo apt-get install php-imagick


sudo apt install php7.3-gmp
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php7.3-fpm

sudo apachectl stop
sudo a2enmod proxy_fcgi setenvif

sudo a2dismod php7.3
   # This disables mod_php.
sudo a2dismod mpm_itk
sudo a2dismod mpm_prefork 
   # This disables the prefork MPM. Only one MPM can run at a time.
sudo a2enmod mpm_event
   # Enable event MPM. You could also enable mpm_worker.

   #added the following text to the end of
sudo nano /etc/apache2/apache2.conf
Protocols h2 h2c http/1.1

sudo a2enconf php7.3-fpm 

sudo a2enmod rewrite

sudo systemctl restart apache2

```

# Setting up apache links
Create a new apache virtual host
```
cd /etc/apache2/sites-available/
sudo nano tribe.conf
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

# Localhost redirects

You will need to setup a redirect on your local so your browser will open your virtual host.
```
sudo nano /etc/hosts
```
and add the following line to the file:
```
127.0.1.1       tribe.localhost
```

Finally restart apache
```
sudo systemctl reload apache2
```

# Unattended updates

Note:
If you are setting up a stand alone server, remember to set security auto update. 
```
sudo apt install unattended-upgrades
```

Then check that desired systems updates are turned on:
```
sudo nano /etc/apt/apt.conf.d/50unattended-upgrades
```

Make sure that at minimum the Security updates are being istalled.
```
Unattended-Upgrade::Allowed-Origins {
        "${distro_id}:${distro_codename}";
        "${distro_id}:${distro_codename}-security";
//      "${distro_id}:${distro_codename}-updates";
//      "${distro_id}:${distro_codename}-proposed";
//      "${distro_id}:${distro_codename}-backports";
};
```

Then set what auto updates are run
```
sudo nano /etc/apt/apt.conf.d/20auto-upgrades
```
Basic options for daily updates and a weekly clean.
```
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
```

# HTTPS setup with CertBot

If you wish to run on https you can set it up for free with:
```
sudo apt-get install python-certbot-apache 
```

To install the cetificate run certbot, then select the site you wish to install the server against. 
```
sudo certbot
```

To renew certificates simply run:
```
sudo certbot renew
```

For complex calls use, e.g. wildcards, multisubdomains
```
sudo certbot --server https://acme-v02.api.letsencrypt.org/directory
```

# Swap
If you are setting up a server from scratch, don't forget to add a swapfile to the system.

First check if there is already a swap file.
```
free -h
```

**WARNING: If there is a swap file, do not continue.**

Your swap file should be roughly twice the size of you ram. But this depends on your ram size.
In the line below 8G means an 8 gig swap file. 

```
sudo fallocate -l 8G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
```

Check you swap file is working
```
free -h
```

Lastly to ensure you swap file is working on reboot add it to to fstab.
To be safe we going to bak up fstab first
```
sudo cp /etc/fstab /etc/fstab.bak
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

# Memcached

Currently we only use memcached on our wordpress server. To install:

```
sudo apt-get install memcached
sudo apt-get install php-dev php-pear libapache2-mod-php
sudo apt-get install php-memcached 
```

For some reason I found this only work after a system reboot.
