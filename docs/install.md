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
Clone the git repository to your local. The final tribe is the folder name so can be named as you need. due to security you will need to first create the folder and give yourself access to it.

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

Composer install will add all external modules. 
```
composer install
```

