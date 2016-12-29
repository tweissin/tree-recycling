# tree-recycling
This contains code to facilitate Boy Scout Christmas tree recycling.

## Getting started

1. Copy tree-helper.properties.template to tree-helper.properties
2. Get a Google Maps API key.
3. Establish a username/password for the 
3. Change tree-helper.properties as needed for your environment

## To debug JNLP 
Go to Java Control Panel > View > Runtime Parameters and set this
    
    -agentlib:jdwp=transport=dt_socket,address=8115,server=y,suspend=n
    
Then run 
    
    javaws http://trees.troop4hopkinton.org/driver/admin/app.jnlp

Then you can attach a debugger to port 8115. 

## Tips on JNLP
If you upload a change to the JAR file, you have to delete it from the local cache:

Java Control Panel > General > Temporary Internet Files: View > Tree Route Creator > 'X' to delete it