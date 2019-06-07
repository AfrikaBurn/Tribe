# Tribe Platform
This is our legacy platform, which currently handles user and project registrations. 

Our actively developed platform is [![TMI](https://github.com/AfrikaBurn/TMI)] and is where all tasks from this system will be migrated to. 

The Tribe platform was built as a responsive system, to handle the practical needs of AfrikaBurn with little to no design time before implementation. 

This guide currently only covers Ubuntu but should be almost identical on Windows.

# Quick install
The quick installer assumes you have a working lamp server and git. 

To replicate our lamp server install, please go [![here](https://github.com/AfrikaBurn/tribe/blob/master/docs/lamp.md)].

One can also follow our detailed installation guide [!here](docs/install.md)]

## Composer
Composer is a package manager used by Drupal to handle public extension modules.

```
sudo apt install composer 
```

## Drush
Drupal's command line controller, used in tandem with Composer. 
The installation guide can be found [![here](https://github.com/drush-ops/drush-launcher)].

Note: We have had issues with Composer installing Drush, so advise the use of the launcher.

## Tribe
Clone the git repository to your local. The final tribe is the folder name so can be named as you need. 

On a default Ubuntu install we work in the www folder
```
cd /var/www
git clone https://github.com/AfrikaBurn/tribe.git tribe
```

Add a secure folder.
```
mkdir tribe_secure
```

Edit git config to ignore file mode changes
```
cd tribe
vim .git/config
```
Set file mode to false
```
filemode = false
```

Set your branch to develop if you want to stay abreast of the latest changes.
```
git branch --set-upstream-to=origin/develop
git pull
```

Composer install will add all external contributed modules. 
```
composer install
```

May 2019
There is 1 patch that needs to be installed and can be found in web/patches:
* collapsiblock

Apply these patches by:
```
cd web/modules/contrib/collapsiblock/
patch -p0 < ../../../patches/fix-collapsiblock.patch
cd ../../../..
```

Make sure you have a database and user setup in MariaSQL / MySQL

You can do this by opening MySQL
```
sudo MySQL
```
Then inside MySQL add a database, user and password.

Note: if you install via the command line, password special characters do not work. 
```
CREATE DATABASE my_database;
CREATE USER 'my_user'@'localhost' IDENTIFIED BY 'my_password';
GRANT ALL PRIVILEGES ON my_user.* TO 'my_database'@'%' IDENTIFIED BY 'my_password' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

May 2019
There is 1 patch that needs to be installed and can be found in web/patches:
* collapsiblock

Apply these patches by:
```
cd web/modules/contrib/collapsiblock/
patch -p0 < ../../../patches/fix-collapsiblock.patch
patch -p0 < ../../../patches/collapsiblock-null-blocks-2992043-2.patch
cd ../../../..
```

To install it's much quicker to use Drush but you can also go to your localhost and install through the user interface. This also crashed for me on the last page, though the install did work. ("drush uli" generated the a password reset link)
```
drush site-install config_installer
```
Note: I an error message when installing, more details in the full installation guide [!here](docs/install.md)].

Goto your localhost and check that it worked. You should have received a username and login in the command.

You can also do a password reset with:
```
drush uli
```

If your login works, we going to import final elements with cim:
```
drush cim
```
