# EnviroSense
EnviroSense is a tool with a REST API and a user interface for collecting and visualizing home consumption data.

# Screenshots

![Dashboard](/doc/images/01-home.png)

![Example Screen](/doc/images/02-solar.png)

# Getting Started
Copy the .env file to .env.local and configure your environment.

EnviroSens requires PHP 8.2 or higher, symfony 7.2 and a database (MySQL, MariaDB, PostgreSQL)

There is a Makefile included in the project which covers the most basic tasks. With the following command an overview of the available targets will be displayed.
```
$ make help
```

# Makefile Targets
### Install
Install composer dependencies and assets.
```
$ make install
```

### Prepare Database
This target creates the database according to the defined database in `.env.local file` and the available migrations will be executed.
> [!NOTE]
> The defined user must have the permission to create databases.
```
$ make prepare-db
```

### Load Test Data
Loads the data which is defined in the `DataFixtures` folder.
```
$ make load-test-data
```

### Backup Remote Database
Dumps the contents of the remote database to a local sql file. A SSH tunnel will be used to execute the mysqldump command.
The following environment variables can be defined.

| Environment variable | Description                                                 | Default value        |
|----------------------|-------------------------------------------------------------|----------------------|
| `SSH_PORT`           | SSH port for creating the tunnel to the database server.    | `8122`               |
| `LOCAL_PORT`         | The local port for binding the mysql listener               | `3336`               |
| `USER`               | SSH User                                                    |                      |
| `HOST`               | IP or hostname of the database server                       |                      |
| `MYSQLDUMP`          | Binary of the mysqldump executable                          | `mysqldump`          |
| `DATABASE`           | The name of the remote database which should be downloaded. | `sfinternetofthings` |
| `REMOTE_MYSQL_USER`  | Username of the remote database                             | `root`               |
```
$ make backup-remote-db ENV=value
```

### Import SQL Dump
Imports the contents of the previous dumped database to the local database server.

| Environment variable | Description                                                                                      | Default value        |
|----------------------|--------------------------------------------------------------------------------------------------|----------------------|
| `DATABASE`           | The database which should be imported. The Makfile searches for a file with the extension `.sql` | `sfinternetofthings` |
| `MYSQL`              | Binary of the mysql executable                                                                   | `mysql`              |
| `LOCAL_MYSQL_USER`   | Username of the local database                                                                   | `root`               |
```
$ make import-remote-db ENV=value
```

### Migrate database
Copies and migrates the previously imported database to the new database.

| Environment variable | Description                    | Default value |
|----------------------|--------------------------------|---------------|
| `CURRENT_DATABASE`   | The new database               |               |
| `MYSQL`              | Binary of the mysql executable | `mysql`       |
| `LOCAL_MYSQL_USER`   | Username of the local database | `root`        |
```
$ make migrate-remote-db ENV=value
```

### Drop DB's
Drops all the project related databases.
> [!CAUTION]
> Once you delete the databases, there is no going back. Please be certain.

| Environment variable | Description                    | Default value        |
|----------------------|--------------------------------|----------------------|
| `CURRENT_DATABASE`   | The new database               |                      |
| `TEST_DATABASE`      | The testing database           |                      |
| `DATABASE`           | The transfer database          | `sfinternetofthings` |
| `MYSQL`              | Binary of the mysql executable | `mysql`              |
| `LOCAL_MYSQL_USER`   | Username of the local database | `root`               |

```
$ make drop-dbs
```

### Run Local Server
> [!NOTE]
> When you start the application for the first time, the TailwindCSS binary will be downloaded initially. This process takes a few seconds, and the application will only be accessible after the download is complete.
```
$ make run
```

### Clean
Clears cache and log files
```
$ make clean
```


### Deploy
Deploys the application to the server

| Environment variable | Description                           | Default value        |
|----------------------|---------------------------------------|----------------------|
| `REMOTE_USER`        | SSH User                              |                      |
| `HOST`               | IP or hostname of the database server |                      |
| `SSH_PORT`           | The SSH Port                          |                      |
```
$ make deploy
```


# Symfony Scripts

```
$ bin/console app:recalc-solar-daily
```

This script recalculates the total column for the `solar_daily` table. This column was not filled within the old database and in order to keep the same structure for every `*_daily` tables there is also a total column for this table.
This script must only run once the transition from the old database to the new database is complete.

```
$ bin/console app:calc-power-daily
```
The smart meter delivers only the current power consumption and the overall consumption.