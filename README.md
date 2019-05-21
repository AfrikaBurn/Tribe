# Tribe Platform
This is our legacy platform, which currently handles user and project registrations. 

Our actively developed platform is [![TMI](https://github.com/AfrikaBurn/TMI)] and is where all tasks from this system will be migrated to. 

The Tribe platform was built as a responsive system, to handle the practical needs of AfrikaBurn with little to no design time before implementation. 

This guide currently only covers Ubuntu but should be almost identical on Windows.

# Quick install
The quick installer assumes you have a working lamp server and git. 

To replicate our lamp server install, please go here.

## Composer
Composer is a package manager used by Drupal to handle public extension modules.

Ubuntu
```
sudo apt install composer 
```

## Drush
Drupal's command line controller, used in tandem with Composer. 
The installation guide can be found [![here](https://github.com/drush-ops/drush-launcher)].

Note: We have had issues with Composer installing Drush, so advise the use of the launcher.

## Tribe
Clone the git repository to your local. The final tribe is the folder name so can be named as you need. 

```
git clone https://github.com/AfrikaBurn/tribe.git tribe
```
Then enter the folder and do a composer install, to add all external modules. 

```
cd tribe_media/

```
