# INSTALLATION

## TABLE OF CONTENTS
- [Before you begin](#before-you-begin)
- [Manual installation](#manual-installation)
    - [Requirements](#requirements)
    - [Setup application](#setup-application)
    - [Configure your web server](#configure-your-web-server)
    - [Localhost Installation](#local-installation)


## Before you begin
1. If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

2. Install composer-asset-plugin needed for yii assets management
```bash
composer global require "fxp/composer-asset-plugin"
```

3. Install NPM or Yarn to build frontend scripts
- [NPM] (https://docs.npmjs.com/getting-started/installing-node)
- Yarn (https://yarnpkg.com/en/docs/install)

### Get source code
#### Download sources
https://github.com/S86Agency/helloiamelliot/archive/master.zip

#### Or clone repository manually
```
git clone https://github.com/S86Agency/helloiamelliot.git
```
#### Install composer dependencies
```
composer install
```

## Manual installation

### REQUIREMENTS
The minimum requirement by this application template that your Web server supports PHP 7.0
Required PHP extensions:
- intl
- gd
- mcrypt
- com_dotnet (for Windows)

### Setup application
1. Copy `.env.dist` to `.env` in the project root.
2. Adjust settings in `.env` file
	- Set debug mode and your current environment
	```
	YII_DEBUG   = true
	YII_ENV     = dev
	```
	- Set DB configuration
	```
	DB_DSN           = mysql:host=127.0.0.1;port=3306;dbname=elliot_db
	DB_USERNAME      = user
	DB_PASSWORD      = password
	```

3. Run in command line
```
php console/yii app/setup
npm install
npm run build
```
4. Copy .htaccess.dist to .htaccess in the project root.



Adjust settings in `backend/config/web.php` file
```
    ...
    'components'=>[
        ...
        'request' => [
            'baseUrl' => '/admin',
        ...
```
Adjust settings in `frontend/config/web.php` file
```
    ...
    'components'=>[
        ...
        'request' => [
            'baseUrl' => '',
        ...
```

## Local installation(Installing SSL and Setting subdomain )

#### 1. Download & Install

  Download and install WampServer from [here](http://www.wampserver.com/en/#download-wrapper).
    *If you need to install 64bit WampServer you must install both of vcredist(x64 and x86)*
   
  Once you installed WampServer successfully server should run correctly on your PC. 
    *If you have problem with running the server it is related to vcredist mostly*
   
#### 2. Setting Virtual Hosts   
  Let's change `localhost` into `s85.co` for convenience.
  
  Uncomment `Include conf/extra/httpd-vhosts.conf` in `c:/wamp64/bin/apache/apache2.4.27/conf/httpd.conf`.
  
  Overwrite `c:/wamp64/bin/apache/apache2.4.27/conf/extra/httpd-vhosts.conf` in to the following.
  
  ```
  # Main domain
  <VirtualHost *:80>
      ServerAdmin webmaster@s85.co
      DocumentRoot "C:/wamp64/www/elliot"
      ServerName elliot.localhost
      ServerAlias *.elliot.localhost
      ErrorLog "${INSTALL_DIR}/logs/s85.co-error.log"
      CustomLog "${INSTALL_DIR}/logs/s85.co-access.log" common

      <Directory "C:/wamp64/www/elliot/">
          Order allow,deny
          Allow from all
      </Directory>
  </VirtualHost>

  ```
  
  Append the following to `c:/Windows/System32/drivers/etc/hosts`.
  
  ```
  127.0.0.1       elliot.localhost
 
  ```
   
#### 3. Configure SSL

  Refer to this [guide](https://www.proy.info/how-to-enable-localhost-https-on-wamp-server/).
  
  *Pay attention the following changes in `httpd-ssl.conf`*
  ```
  DocumentRoot "c:/wamp64/www/elliot"
  ServerName s85.co:443
  ```
  
  You can check sample configuration files.
  [c:/wamp64/bin/apache/apache2.4.27/conf/httpd.conf](https://drive.google.com/open?id=0B-8Yx0D7qrlzMTVwa3F1cGktSjQ)
  [c:/wamp64/bin/apache/apache2.4.27/conf/extra/httpd-vhosts.conf](https://drive.google.com/open?id=0B-8Yx0D7qrlzZXMtNVlneHBGb0U)
  [c:/wamp64/bin/apache/apache2.4.27/conf/extra/httpd-ssl.conf](https://drive.google.com/open?id=0B-8Yx0D7qrlzX0hDb3lLVWs4VHc)
  [c:/Windows/System32/drivers/etc/hosts](https://drive.google.com/open?id=0B-8Yx0D7qrlzQkpINnhVazg4VVE)
  
## Install MySQL

  We need to have main database([`elli`](https://drive.google.com/open?id=0B-8Yx0D7qrlzeVh0RFR6UUZsbms)) and user database([`<username>_elliot`](https://drive.google.com/open?id=0B-8Yx0D7qrlzdHRmbHVIaGpSbEk)).


## Configure Source

  Rename source folder into `elliot` and copy it to `c:\wamp64\www`.
  
  In order that application run on local server correctly we need to add Yii configuration files since those were ignored on Git repo.
  You can download configuration files [here](https://drive.google.com/open?id=0B-8Yx0D7qrlzVk5aZElZVl91YXM) 
  
  
  
**All configuration is correct you should see local server is running on `https://s85.co`**  
