# DBServerSyncServices
###  2021 PaulNovack  - BSD License 2.0 - notes at bottom of README.md


A general purpose configurable Backup Service and Restore Service to sync databases 
between servers with options for Full database backups or table by table backups.
Includes conditions to allow a "wait" for other operations to complete 
before backing up a list of tables based on a sql statement that evaluates 
to true or false.  Configuration also allows for exclusion of tables that do not need to be synced.


### Running the ServerSynServices:

There are 2 separate php processes that must run for DB Server Synchronization
<ol>
<li> BackupServiceJob.php - This process is resposible for 
iterating thru the databases and creating a *.sql file 
using mysqldump cli and the configuration in the 
SourceServerConfig.json 
file and the Default_SyncJobSettings.json file or the config file name passed as an argument at runtime</li>
<br/>

<li> RestoreServiceJob.php - This process is responsible for 
iterating thru directories as specified in the Default_SyncJobSettings.json finding *.sql files produced by 
the BackupServiceJob.php and actually importing them into the destination database specified in DestinationServerConfig.json using mysql cli.  
This may be run as a continually running process that will simply watch 
for *.sql files and process them as they arrive.
</li>
</ol>

### General notes about usage of this Service:


<br/>
<li> <strong>Data files directories</strong> - 
The service will automatically create the directory 
structure to store files.   After import the service will 
remove all sql files.</li>
<br/>
<li> <strong>Import database</strong> - 
The service will <strong>NOT</strong> create the database used for importing data into on the destination system.
This is a purposefully built safety mechanism to prevent accidental mis-configuration that would overwrite the source databases.
The database specified in the Default_SyncJobSettings.json in the field "tempDatabase" must be manually created on the destination mysql server."  
Data tables will be imported into this temporary database then moved into the correct database.</li>

## BSD License 2.0

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.[8]
