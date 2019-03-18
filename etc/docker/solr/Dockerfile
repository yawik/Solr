#
FROM solr:6.1
MAINTAINER https://github.com/muccg/

RUN cd /opt/solr/server/lib && curl -O http://central.maven.org/maven2/com/vividsolutions/jts/1.14/jts-1.14.jar

RUN mkdir -p /opt/solr/data
RUN chown solr:solr /opt/solr/data