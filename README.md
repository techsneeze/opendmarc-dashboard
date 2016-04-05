# opendmarc-dashboard
A PHP dashboard to view the opendmarc database information, which has been used to send reports to other domains.

## Installation and Configuration

NOTE: The dashboard expects that you have already populated a database using the [OpenDMARC Tools](http://www.trusteddomain.org/opendmarc/)

Download the required files:
```
git clone https://github.com/techsneeze/opendmarc-dashboard.git
```

Once the PHP files ave been downloaded, you will need to copy `opendmarc-dashboard-config.php.sample` to `opendmarc-dashboard-config.php`.  

```
cp opendmarc-dashboard-config.php.sample opendmarc-dashboard-config.php
```

Next, edit these basic configuration options in the `opendmarc-dashboard-config.php.sample` file with the appropriate information:

```
$dbhost="localhost";
$dbname="opendmarc";
$dbuser="opendmarc";
$dbpass="xxxxxxxxx";
```

Ensure that `opendmarc-dashboard-config.php`, `opendmarc-dashboard.php`, and `default.css` are all in the same folder.

## Usage

Navigate in your browser to the location of the `opendmarc-dashboard.php` file.

You should be presented with the basic dashboard view, allowing you to navigate through the records that have been stored.

The default view shows the 90 most recent records in the database.  To change the number of records displayed, append `?limit=x` to the end of the URL, where `x` is the nubmer of records desired. 
* Example: Use `opendmarc-dashboard.php?limit=200` to display 200 records.


