# Solr

YAWIK entities can be searched with the full text function of mongodb. This feature is great and usually sufficient, for
example, to offer jobs on your own career site. If you want to use YAWIK as a job board, the requirements increase. A
job exchange must be able to provide many jobs to many visitors. You need a search engine that scales. Currently Solr is
supported.

With the solr module, the search is outsourced to the Solr search engine. The module synchronizes the search index with
the jobs from the MongoDB.

## Requirements

current development is using:

* php5-solr (pecl >= 2.4.0)
* apache solr

!!! note 
    Debian 8 ships with php5-solr 1.0.2. You can build your solr extension by:

``` bash
aptitude install php5-dev libcurl4-openssl-dev libxml2-dev
pecl install solr
echo "extension=solr.so" > /etc/php5/mods-available/solr.ini
php5enmod solr
php -m| grep solr # should show the activated solr extension
```


Good resources on how to install solr:

* https://cwiki.apache.org/confluence/display/solr/Installing+Solr
* http://nl3.php.net/manual/en/solr.installation.php

Here is the way we've installed it in our Demo. First, you need JAVA. On Debian 8 you can install it via:

``` bash
apt install -t jessie-backports  openjdk-8-jre-headless ca-certificates-java
```

then get a binary version of solr. The binary package contains an installation script. So unzip/untar it and execute the
installation script. By default you'll find your solr server in :file:`/opt/solr/`. The solr data are stored in
:file:`/var/solr/data/`. After the installation you can remove the downloaded and extracted files.


```
wget https://www-us.apache.org/dist/lucene/solr/6.6.5/solr-6.6.5.tgz
tar xzf solr-6.6.5.tgz
solr-6.6.5/bin/install_solr_service.sh solr-6.6.5.tgz
```


After the installation, solr server ist running at localhost port 8983. This is enough for yawik to be able the access
the solr Server.

If you want to be able the access the solr frontend via https without touching the solr installation at all, an apache
proxy may be a solution. If you want to use this solution, you have to enable the apache proxy module.


```
    > a2enmod proxy proxy_http
```

For setting up an apache Proxy you can use a Virtual Host which looks like

```
    <VirtualHost *:8443>

         ProxyRequests Off
         <Proxy *>
            AuthType Basic
            AuthName "Solr Search"
            AuthBasicProvider file
            AuthUserFile /etc/apache2/solr.passwd
            Require valid-user
            Order deny,allow
            Allow from all
         </Proxy>

         ProxyPass / http://localhost:8983/
         ProxyPassReverse / http://localhost:8983/

    </VirtualHost>
```

Set the the user/pass in :file:`/etc/apache2/solr.passwd` via :command:`htpasswd /etc/apache2/solr.passwd username`





## Installation

to install the [yawik/solr](https://github.com/yawik/Solr) Modul into a running YAWIK, change into the `YAWIK/modules` directory and clone
the yawik/solr module .

```
 git clone https://github.com/yawik/Solr
```

To activate the module create a php file named ``WhateverYouWant.module.php`` in your config autoload directory containing:

```
 <?php
 return ['Solr'];
```

To configure the solr connection copy the Solr options file into you autoload directory and adjust the values.

## Configuration

|     Option              |   Type       |    Description                                                                      |
|-------------------------|--------------|-------------------------------------------------------------------------------------|
|     secure              |   bool       |    Use SSL (https) [true] or not [false]                                            |
|     hostname            |   string     |    Hostname of the solr server                                                      |
|     port                |   int        |    The TCP port. Default: 8983                                                      |
|     username            |   string     |    Username used for HTTP Authentication (if needed)                                |
|     password            |   string     |    Password of the HTTP Authentication                                              |
|     jobsPath            |   string     |    The path to the solr jobs index                                                  |
|     facetFields         |   array      |    List of facet fields. Each entry must be an array with at least the key "name".<br>An optional key "label" specifies a headline for the factes results.             |
|     facetLimit          |   int        |    Maps to facet.limit. Sets the maximum number of constraint counts that should be returned for the facet fields.                                                   |
|     facetMinCount       |   int        |    Sets the minimum counts for facet fields that should be included in the response |
|     parameterNames      |   array      |    Maps query parameter names to solr field names. (see [dist file](https://github.com/yawik/Solr/blob/master/config/solr.moduleoptions.local.php.dist) for examples) |
|     sorts               |   array      |    Specify the sort order used for an empty search.<br>Must be an associated array where the key is the field name and the value  is the sort order.<br>(Default: [ "datePublishStart" => \SolrQuery::ORDER_DESC ])                      |
|     filterQueries       |   array      |    todo                                                                             |
|     boostQueries        |   array      |    todo                                                                             |



``` bash
  cp module/Solr/config/solr.moduleoptions.local.php.dist config/autoload/solr.moduleoptions.local.php
```

!!! note
    Solr needs a schema. The schema is currently a work in progress. You can use the schema in [Solr/contrib](https://github.com/yawik/Solr/tree/master/contrib).


!!! note
    at least one field needs JTS. So if you want to use the contributed schema, you have to install JTS
    via:

    ```
        mkdir tmp
        cd tmp
        wget wget https://downloads.sourceforge.net/project/jts-topo-suite/jts/1.14/jts-1.14.zip
        unzip jts-1.14.zip
        cp lib/*.jar /opt/solr-6.6.0/server/solr-webapp/webapp/WEB-INF/lib/
    ```

    Or take a look at the issue https://github.com/yawik/Solr/issues/4 . Maybe the fiels using JTS is not needed by
    YAWIK any more.


If you want to set a user/password for solr you first have to enable an [Authorization
Plugin](https://lucene.apache.org/solr/guide/6_6/basic-authentication-plugin.html).  Since Solr6 you can do
so by copying the following json to :file:`/var/solr/data/security.json`

```
    {
    "authentication":{
       "blockUnknown": true,
       "class":"solr.BasicAuthPlugin",
       "credentials":{"solr":"IV0EHq1OnNrj6gvRCwvFwTrZ1+z1oBbnQdiVC3otuq0= Ndd7LKvVBAaZIF0QAVi1ekCfAJXr1GGfLtRUXhgrF8c="}
    },
    "authorization":{
       "class":"solr.RuleBasedAuthorizationPlugin",
       "permissions":[{"name":"security-edit",
          "role":"admin"}],
       "user-role":{"solr":"admin"}
    }}
```

This will add a user "solr" with the password "SolrRocks". After that you can change the password with

```
    curl --user solr:SolrRocks http://localhost:8983/solr/admin/authentication -H 'Content-type:application/json' \
        -d '{"set-user": {"solr" : "myPassword"}}'
```


you can initially index all active jobs by:

``` bash
 bin/console solr index job
```

## Schema

| fields                           | description|
|----------------------------------|----------------------------------------------------------------------|
| id                               | Primary key|
| applyId                          | unique ID for the application|
| title                            | Job title|
| city                             | city of the job opening|
| dateCreated                      ||
| datePublishStart                 ||
| datePublishEnd                   ||
| dateModified                     ||
| lang                             | language of the job opening|
| location                         | location of the job posting (string)|
| organizationName                 | name of the hiring Organziation|
| companyLogo                      | logo of the hiring Organization|
| entityName                       | possible values "job" or "location"|
| *_MultiString*                   | Used by facets. E.g. region_MultiString, industry_MultiString, profession_MultiString|





## Parameters

Since 0.9 you can use the following parameters to search


| parameter                         | value |
|-----------------------------------|---------------------------|
| l                                 |   location|
| d                                 |   distance|
| q                                 |   query|
| organizationTag                   |   company|
| profession_Mutistring             |   profession|
| industry_Multistring              |   industry|
| employmentType_Multistring        |   employmentType|
