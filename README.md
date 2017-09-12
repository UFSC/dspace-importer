# dspace-importer
Migration tool for DSpace. Collects data from other repositories and submit to dspace

#Requirements

* Zend Framework
* SimpleXML

#Usage

Configure your options in config/config.ini

Importing from Pergamum:

php src/Pergamum2DSpace.php -c config/config.ini