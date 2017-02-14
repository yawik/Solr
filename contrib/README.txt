The Solr extension is currently running using a solr 6.1 Server with the
config files located in conf/.

Note 1
######

The collections contains a schema.xml with a lot of fields redundant
fields. Eg. there are the fields 'latLon', 'point', 'points', 'locations',
'geoLocation', which all contain some spatial data. Reason for this, is
that we're currently playing around with the solr features. 

All fieldnames will propably change in future versions


Warning
#######

The config files are based on the default solr examples of a former solr version
(3.x I guess). Don't use them on a production Server

