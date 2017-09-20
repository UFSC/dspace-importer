# dspace-importer
Migration tool for DSpace. Collects data from other repositories and submit to dspace

# Requirements

* Zend Framework 3
* DSpace 6.1

# Usage

* Configure your options in config/config.ini and pergamumws/parametros.php

* Deploy the folder pergamumws in a Web server that supports PHP

* Importing from Pergamum:

 php src/Pergamum2DSpace.php -c config/config.ini >> /var/log/somelogfile.log