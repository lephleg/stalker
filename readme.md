# Stalker

## Project Description

**Stalker** is a PHP application based on Laravel 5.6, that will hunt you down and track every visit you may make on a website using its tracking code.

_TODO blah blah_

## Application Setup Instructions

Stalker comes with a ready-to-deploy Docker stack, also included in this repository. 

**Prerequisites:** 

* Depending on your OS, the appropriate version of Docker Community Edition has to be installed on your machine.  ([Download Docker Community Edition](https://www.docker.com/community-edition#/download))
* A bash terminal (or a decend terminal emulator like [Cmder](http://cmder.net/) on Windows)

**Installation:**

1. Clone the project in your user's home directory, where your user has full read/write access.

2. In repository root, create two new textfiles named `db_root_password.txt` and `db_password.txt` and place your preferred database passwords inside:

	```
	$ echo "g0su_pWd" > db_root_password.txt
    $ echo "h@su_pwD" > db_password.txt
	```
    
3. Set up config file:

	Make a copy of `.envexample` file named `.env`. Update copied file, with your database credentials and other favored settings. 
    
    Here's a preview of the `.envexample`:
	
	```
    APP_NAME=Stalker
    APP_ENV=local
    APP_KEY=
    APP_DEBUG=true
    APP_URL=http://localhost
    
    LOG_CHANNEL=daily
    
    DB_CONNECTION=mysql
    DB_HOST=stalker-mysql
    DB_PORT=3306
    DB_DATABASE=stalkerdb
    DB_USERNAME=stalker
    DB_PASSWORD=<secret_placed_in_db_password.txt>
    
    BROADCAST_DRIVER=log
    CACHE_DRIVER=file
    SESSION_DRIVER=file
    SESSION_LIFETIME=120
    QUEUE_DRIVER=sync
	```

4. Spin up the containers:
    
	```
	$ docker-compose up -d
	```

5. Entrypoint script

    After the whole stack is up, an *entrypoint* script will hanlde the initial setup of Laravel inside the **stalker-app** container. You'll have to wait for this script to complete all its operations (grab a raki shot with some meze). 

    You can monitor its progress by tailing its logfile with the following command (a *"Completed."* message shall appear when everything's done): 

    ```
    $ docker exec -it stalker-app tail -f /var/log/stalker/entrypoint.log
    ```

6. That's it! Navigate to [http://localhost](http://localhost) to access the application.
