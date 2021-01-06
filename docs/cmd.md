# Commandline

you can use the ./vendor/bin/yawik tool to rebuild the solr index

```
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
Solr
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

  yawik solr index job    Indexing active jobs                                                                                                           

  --batch=<int>        Indexing jobs in batches of <int> jobs.                                                                                                                 
                       Each invokation will continue the indexing with the next batch.                                                                                         
                       When the last batch is indexed, it exists with a non-zero exit code.                                                                                    
                       So you can do something like:                                                                                                                           
                       while true; do [yawik] solr index job --batch 2500 || break; done                                                                                       
                                                                                                                                                                               
  --orgId=<MongoId>    Only index the jobs from the specified organization                                                                                                     
                       by its id.                                                                                                                                              
                                                                                                                                                                               
  --drop               Prior to index jobs, delete all indexed jobs from the solr index.                                                                                       
                       Only works with --orgId  
```