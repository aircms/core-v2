# Setup Apache, PHP & MongoDB on Ubuntu 22.04

## Prerequisites
- Fresh install of Ubuntu 20.04
- User privileges: root or non-root user with sudo privileges

## Step 1. Update the System
Before the installation of these three different services we are going to update the system packages to the latest versions available:


```console
sudo apt update -y && sudo apt upgrade -y
```

## Step 2. Install Apache Web Server and modules
### Step 2.1. Install Apache
To install the Apache webserver execute the command below:

```console
sudo apt install apache2
```

After successfully installation, start and enable the service

```console
sudo systemctl start apache2 && sudo systemctl enable apache2
```

To check if everything is OK, execute the following command for the status of the Apache2 service:

```console 
sudo systemctl status apache2
```

You should receive the following output:

```console
● apache2.service - The Apache HTTP Server
     Loaded: loaded (/lib/systemd/system/apache2.service; enabled; vendor preset: enabled)
     Active: active (running) since Thu 2024-03-21 13:17:02 UTC; 1min 33s ago
       Docs: https://httpd.apache.org/docs/2.4/
    Process: 7964 ExecStart=/usr/sbin/apachectl start (code=exited, status=0/SUCCESS)
   Main PID: 7968 (apache2)
      Tasks: 6 (limit: 18677)
     Memory: 12.2M
        CPU: 42ms
     CGroup: /system.slice/apache2.service
             ├─7968 /usr/sbin/apache2 -k start
             ├─7972 /usr/sbin/apache2 -k start
             ├─7973 /usr/sbin/apache2 -k start
             ├─7974 /usr/sbin/apache2 -k start
             ├─7975 /usr/sbin/apache2 -k start
             └─7976 /usr/sbin/apache2 -k start

Mar 21 13:17:02 live systemd[1]: Starting The Apache HTTP Server...
Mar 21 13:17:02 live apachectl[7967]: AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 127.0.>
Mar 21 13:17:02 live systemd[1]: Started The Apache HTTP Server.
```

### Step 2.2. Install additional modules for Apache

```console
sudo a2enmod headers 
sudo a2enmod expires 
sudo a2enmod rewrite 
sudo a2enmod ssl 
systemctl restart apache2
```

## Step 3. Install PHP with modules
First, we need to add the PHP repository and choose Apache from the list. Execute the following command:

```console
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
```
Once, the repo is added, update the system and install the PHP along with other modules including the MongoDB module.

```console
sudo apt install php
```

Check the installed PHP version with the following command:

```console
php -v
```
You should receive the following output (In your case, the version may be different):

```console
PHP 8.3.4 (cli) (built: Mar 16 2024 08:40:08) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.4, Copyright (c) Zend Technologies
    with Zend OPcache v8.3.4, Copyright (c), by Zend Technologies
```

Install additions PHP modules:
```console
sudo apt install libapache2-mod-php
sudo apt install php-cli
sudo apt install php-common
sudo apt install php-mongodb
sudo apt install php-imap
sudo apt install php-mbstring
sudo apt install php-gd
sudo apt install php-curl
```

## Step 4. Install MongoDB Database Server
First, install the prerequisite packages needed during the installation. To do so, run the following command:
```console
wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | sudo apt-key add -
```

To install the most recent MongoDB package, you need to add the MongoDB package repository to your sources list file on Ubuntu. Before that, you need to import the public key for MongoDB on your system using the wget command as follows:

```console
curl -fsSL https://pgp.mongodb.com/server-7.0.asc |  sudo gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg --dearmor
```

Next, add MongoDB 7.0 APT repository to the /etc/apt/sources.list.d directory:
```console
echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/7.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-7.0.list
```

Once the repository is added, reload the local package index:
```console
sudo apt update
```

The command refreshes the local repositories and makes Ubuntu aware of the newly added MongoDB 7.0 repository.

With that out of the way, install the mongodb-org meta-package that provides MongoDB:
```console
sudo apt install mongodb-org
```

Once the installation is complete, verify the version of MongoDB installed:
```console
mongod --version
```

You should receive the following output
```console
db version v7.0.7
Build Info: {
    "version": "7.0.7",
    "gitVersion": "cfb08e1ab7ef741b4abdd0638351b322514c45bd",
    "openSSLVersion": "OpenSSL 3.0.2 15 Mar 2022",
    "modules": [],
    "allocator": "tcmalloc",
    "environment": {
        "distmod": "ubuntu2204",
        "distarch": "x86_64",
        "target_arch": "x86_64"
    }
}
```

Enable MongoDB service:
```console
systemctl enable mongod.service
```

## Step 5. Install Composer
First, update the package manager cache by running:

```console
sudo apt update
```

Next, run the following command to install the required packages and composer:
```console
sudo apt install php-cli unzip composer
```

To test your installation, run (In your case, the version may be different):
```console
composer
```

```console
   ______
  / ____/___  ____ ___  ____  ____  ________  _____
 / /   / __ \/ __ `__ \/ __ \/ __ \/ ___/ _ \/ ___/
/ /___/ /_/ / / / / / / /_/ / /_/ (__  )  __/ /
\____/\____/_/ /_/ /_/ .___/\____/____/\___/_/
                    /_/
Composer 2.2.6 2022-02-04 17:00:38

Usage:
  command [options] [arguments]

Options:
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --profile                  Display timing and memory usage information
      --no-plugins               Whether to disable plugins.
      --no-scripts               Skips the execution of all scripts defined in composer.json file.
  -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
      --no-cache                 Prevent use of the cache
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  about                Shows a short information about Composer.
  archive              Creates an archive of this composer package.
  browse               [home] Opens the package's repository URL or homepage in your browser.
  check-platform-reqs  Check that platform requirements are satisfied.
  clear-cache          [clearcache|cc] Clears composer's internal package cache.
  completion           Dump the shell completion script
  config               Sets config options.
  create-project       Creates new project from a package into given directory.
  depends              [why] Shows which packages cause the given package to be installed.
  diagnose             Diagnoses the system to identify common errors.
  dump-autoload        [dumpautoload] Dumps the autoloader.
  exec                 Executes a vendored binary/script.
  fund                 Discover how to help fund the maintenance of your dependencies.
  global               Allows running commands in the global composer dir ($COMPOSER_HOME).
  help                 Display help for a command
  init                 Creates a basic composer.json file in current directory.
  install              [i] Installs the project dependencies from the composer.lock file if present, or falls back on the composer.json.
  licenses             Shows information about licenses of dependencies.
  list                 List commands
  outdated             Shows a list of installed packages that have updates available, including their latest version.
  prohibits            [why-not] Shows which packages prevent the given package from being installed.
  reinstall            Uninstalls and reinstalls the given package names
  remove               Removes a package from the require or require-dev.
  require              Adds required packages to your composer.json and installs them.
  run-script           [run] Runs the scripts defined in composer.json.
  search               Searches for packages.
  show                 [info] Shows information about packages.
  status               Shows a list of locally modified packages.
  suggests             Shows package suggestions.
  update               [u|upgrade] Upgrades your dependencies to the latest version according to composer.json, and updates the composer.lock file.
  validate             Validates a composer.json and composer.lock.
```