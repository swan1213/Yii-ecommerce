# Elliot Development Environment Setup on macOS

## Table of Contents

- [Before You Begin](#before-you-begin)
- [Prerequisites](#prerequisites)
    - [Xcode Command Line Tools](#xcode-command-line-tools)
    - [Homebrew](#homebrew)
    - [PHP 7.1](#php-71)
    - [PHP Extensions](#php-extensions)
    - [Composer](#composer)
    - [NPM](#npm)
    - [Yarn](#yarn)
    - [MySQL](#mysql)
- [Codebase Setup](#codebase-setup)
    - [GitHub SSH Key](#github-ssh-key)
    - [Clone and Dependencies](#clone-and-dependencies)
    - [.env File Configuration](#env-file-configuration)
    - [Create Database](#create-database)
    - [DB Migrations](#db-migrations)
    - [NPM Packages](#npm-packages)
- [Apache Configuration](#apache-configuration)
    - [/etc/hosts](#etchosts)
    - [Apache Base Configuration](#apache-base-configuration)
    - [SSL Setup](#ssl-setup)
    - [Virtual Host Setup](#virtual-host-setup)
    - [Start Apache](#start-apache)
- [Create Account](#create-account)

***

## Before You Begin
All commands listed below should be run in the Mac Terminal as your normal user (not root).  This tutorial assumes you will install the Elliot codebase under `$HOME/src/elliot` - and all examples are displayed as such. However, it should be safe to use any path you like.

It seems that these install instructions only work on High Sierra (or higher presumably).  Let us know if it works for you on an earlier version.

***

## Prerequisites

### Xcode Command Line Tools
If you don't have Xcode or the Xcode command-line tools installed:
```
xcode-select --install
```

### Homebrew
If Homebrew isn't installed:
```
ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
```
If you are prompted for a password, enter the password you use to login to your Mac, to authenticate to install tools as root.

### PHP 7.1
Use PHP 7.1 instead of 7.2, because a Homebrew bottle isn't built for php72-mcrypt. If PHP isn't installed:
```
brew update
brew upgrade
brew tap homebrew/php
brew install apr apr-util
brew install php71
```

Important: `/usr/local/sbin` must be before `/usr/sbin` in your PATH.  Edit your `~/.bashrc` (or create it if it doesn't exist) and add:
```
export PATH=/usr/local/sbin:$PATH
```
After editing it, load the change in your shell by doing:
```
source ~/.bashrc
```

### PHP Extensions
To install the intl, mcrypt and gd PHP extensions:
```
brew install gd
brew install php71-intl
brew install mcrypt php71-mcrypt
```

### Composer
If PHP Composer isn't installed:
```
brew install composer
```

### NPM
If NPM isn't installed:
```
brew install npm
```

### Yarn
If Yarn isn't installed:
```
brew install yarn
```

### MySQL
If MySQL isn't installed:
```
brew install mysql
brew services start mysql
```

***

## Codebase Setup

### GitHub SSH Key
Make sure you have your ssh keys setup for GitHub.  If you do not, see the [GitHub SSH key setup](https://help.github.com/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent/).

### Clone and Dependencies
Run these commands to clone the source tree and install dependencies:
```
mkdir -p ~/src/elliot
git clone git@github.com:S86Agency/helloiamelliot.git ~/src/elliot
cd ~/src/elliot
git checkout stage
cp .env.dist .env
composer global require "fxp/composer-asset-plugin"
composer install
```
Note that the ```composer install``` command will prompt you to create a token for GitHub - this is fine - just follow the instructions.  Also note that ```composer install``` will take a long time to run and my seem like it's hung - it's not - you just have to wait.

### .env File Configuration
Edit the file `.env` in the source tree root, and change all instances of `elliot.global` to `elliot.localhost`.

Then, edit the DB connection strings.  If you already had an existing MySQL installation set up with a custom configuration, enter it here.  Otherwise, change `localhost` to `127.0.0.1` in the default config. (I have no idea why `localhost` doesn't work here, but I had to do this on my local.)

### Create Database
Create a database called `elliot_db` on your MySQL installation:
```
echo create database elliot_db\; | mysql -uroot
```

### DB Migrations:
Run this command:
```
php console/yii app/setup
```

### NPM Packages
Run these commands:
```
npm install
npm install jquery
npm run build
```

***

## Apache Configuration

### /etc/hosts
Edit your `/etc/hosts` file (as root) and add the following entry:
```
127.0.0.1 elliot.localhost elliot.elliot.localhost
```

### Apache Base Configuration
This guide uses the Apache version that is installed by default with macOS. 

Edit `/etc/apache2/httpd.conf` and make the following changes:
- Find each of these lines and uncomment them (they appear at different locations within the file):
```
LoadModule php7_module libexec/apache2/libphp7.so
LoadModule ssl_module libexec/apache2/mod_ssl.so
LoadModule socache_shmcb_module libexec/apache2/mod_socache_shmcb.so
LoadModule rewrite_module libexec/apache2/mod_rewrite.so
Include /private/etc/apache2/extra/httpd-vhosts.conf
Include /private/etc/apache2/extra/httpd-ssl.conf
```
- Now find the `<Directory />` section, and change `Require all denied` to `Require all granted`

### SSL Setup
Create a self-signed SSL certificate.  You can fill in whatever information you like when it prompts for Country/City/State/etc.  Run these as root:
```
mkdir /etc/apache2/ssl
openssl req \
    -newkey rsa:2048 \
    -x509 \
    -nodes \
    -keyout /etc/apache2/ssl/server.pem \
    -new \
    -out /etc/apache2/ssl/server.pem \
    -subj /CN=elliot.localhost \
    -reqexts SAN \
    -config <(cat /System/Library/OpenSSL/openssl.cnf \
        <(printf '[SAN]\nsubjectAltName=DNS:elliot.localhost')) \
    -sha256 \
    -days 3650
```

Now edit `/etc/apache2/extra/httpd-ssl.conf`:
- Find the `ServerName` line and change it to `ServerName elliot.localhost:443`
- Change `DocumentRoot` to the path where you installed the Elliot source code.
- Change the `SSLCertificateFile` and `SSLCertificateKeyFile` paths to `/private/etc/apache2/ssl/server.pem`

Now, in Mac Finder, open the folder `/etc/apache2/ssl` and double-click `server.pem`.  Change the `Keychain` drop-down to `System` and click `Add`.  Then in the `Keychain Access` app (which should have automatically opened), click on `System` on the left side, then double click on `elliot.localhost`.  Expand the `Trust` section and change `Secure Sockets Layer (SSL)` to `Always Trust` and close the dialog.

### Virtual Host Setup
Now we are going to setup the virtual host `elliot.localhost`.

Edit `/etc/apache2/extra/httpd-vhosts.conf` and make the following changes:
- There are probably two `<VirtualHost *:80>` sections.  Remove both.
- Add the following section, replacing `<Source Path>` with the path where you installed your source code (such as `/Users/robert/src/elliot`):
```
<VirtualHost *:443>
    ServerAdmin webmaster@elliot.localhost
    DocumentRoot "<Source Path>"
    ServerName elliot.localhost
    ServerAlias *.elliot.localhost
    ErrorLog "/private/var/log/apache2/elliot-error.log"
    CustomLog "/private/var/log/apache2/elliot-access.log" common
    SSLEngine on
    SSLCertificateFile "/private/etc/apache2/ssl/server.pem"
    SSLCertificateKeyFile "/private/etc/apache2/ssl/server.pem"

    <Directory "<Source Path>">
        AllowOverride AuthConfig FileInfo
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

### Start Apache
Next, verify that the Apache configuration is ok:
```
apachectl configtest
```

If everything is fine, you should get the result `Syntax OK`.  If not, correct any errors that are listed.

Then start Apache:
```
sudo apachectl start
```

***

## Create Account
Now you should be ready to actually use the Elliot web app and create an account!  Open your browser directly to `https://elliot.localhost/user/sign-in/signup`

If you get any SSL warning, you can ignore it (will work on this later so there are no SSL errors in Chrome).