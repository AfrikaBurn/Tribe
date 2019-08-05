This is a detailed, step by the step, installation guide. There is a quick installation guide available in the README.md or [[here](https://github.com/AfrikaBurn/tribe)].

 # Tribe Platform
This is our legacy platform, which currently handles user and project registrations. 

Our actively developed platform is [![TMI](https://github.com/AfrikaBurn/TMI)] and is where all tasks from this system will be migrated to. 

The Tribe platform was built as a responsive system, to handle the practical needs of AfrikaBurn with little to no design time before implementation. 

This guide currently only covers Ubuntu but should be almost identical on Windows.

# Full installation guide
To replicate our lamp server install, please go [![here](https://github.com/AfrikaBurn/tribe/blob/master/docs/lamp.md)].

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
Clone the git repository to your local. The final tribe is the folder name so can be named as you need. Due to security, you will need to first create the folder and give yourself access to it.

On a default Ubuntu install we work in the www folder
```
cd /var/www
sudo mkdir tribe
sudo chown YOUR_USERNAME:YOUR_USERNAME tribe
git clone https://github.com/AfrikaBurn/tribe.git tribe
```

Add a secure folder.
```
mkdir tribe_secure
sudo chown www-data:www-data tribe
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

Set your branch to develop if you want to stay abreast of the latest changes. This does cause a lot more noise to also be pulled through, that may just distract if you are focused on a task.
```
git branch --set-upstream-to=origin/develop
git pull
```

If you change your branch and get a merge conflict message, simply check out the file from the correct branch, by adding the relative path and file name. 
```
git checkout origin/develop -- RELATIVE_PATH/FILENAME
```

Composer install will add all external modules. 
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
patch -p0 < ../../../patches/collapsiblock-null-blocks-2992043-2.patch
cd ../../../..
```

## Mysql Setup
Make sure you have a database and user setup in MariaSQL / MySQL

You can do this by opening MySQL
```
sudo mysql
```
Then inside MySQL add a database, user and password.

Note: if you install via the command line, password special characters do not work. 
```
CREATE DATABASE my_database;
CREATE USER 'my_user'@'localhost' IDENTIFIED BY 'my_password';
GRANT ALL PRIVILEGES ON tribe_alt_database.* TO 'tribe_alt_user'@'%' IDENTIFIED BY 'tribe_password' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

## Install Drupal

To install it's much quicker to use Drush but you can also go to your localhost and install through the user interface. This also crashed for me on the last page, though the install did work. ("drush uli" generated a password reset link)
```
drush site-install config_installer
```
It takes a while to complete.

Once complete it spits out a user name and password. 
Now go to your browser:
```
tribe.localhost 
```

If this fails you can check the apache logs, but there are no know hanging errors:
```
tail /var/log/apache2/error.log
```

Select login 
Type in the user name and password.

If you lose the password, one can reset it with:
```
drush uli
```
This will spit out something like:
http://default/user/reset/1/1558539055/mxVEGSTdLjU4_i_iTUjiH7uEus9mL2yy2wvI1b_F8Qg/login
replace "default" with your domain. e.g.
http://tribe.localhost/user/reset/1/1558539055/mxVEGSTdLjU4_i_iTUjiH7uEus9mL2yy2wvI1b_F8Qg/login

You will see a content missing an error message. As there is no content currently on the system

If your login works, we going to ensure everything imported with drush content import. Some features will only trigger after installation is complete.
```
drush cim
```

You should now have a working version of Tribe, but will  

If you need to flush the cache from the command line you can run
```
drush cr
```

and to run cron from the command line
```
drush cron
```
