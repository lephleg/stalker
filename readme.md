# Stalker

### Project Description

**Stalker** is a PHP application based on Laravel 5.6, that will hunt you down and track every visit you may make on a website using its tracking code.

Stalker service uses a javascript snippet like the following, to inject its tracking code on every website on its network:

```
<script>
  (function() {
    stalkerUrl = document.location.protocol + "//stalker.io/sites/<site_id>";
    var stalker = document.createElement("script");
    stalker.type = "text/javascript";
    stalker.async = true;
    stalker.src = stalkerUrl + "/tracking-code?key=<site_key>";
    $(document.body).append(stalker)
  }())
</script>
```

The tracking code uses cookies to identify unique visitors. The flow is more or less the following:

1. A user visits a tracked website. Tracking code checks for the existence of a `vid` cookie on user's browser.
2. If the cookie doesn't exists, a new one is created and as its value a UUID is generated (an [RFC4122](https://www.ietf.org/rfc/rfc4122.txt) version 4 compliant solution has been used). Another cookie `visits_count` will also be created with the initial value of `1`.
3. If the cookie exists, the `visits_count` counter gets increased by `1`.
4. After the cookies have been updated, the following information are being tracked regarding the current visit:

    * User's agent (browser)
    * Page URL
    * Client's datetime
    * Users' IP address

5. To get the public IP address of the user a third-party service is being used ([ipify.org](https://www.ipify.org/)).
6. As soon as the IP address has been received the tracking code sends the tracking data back to Stalker's server to be verified and stored.

The tracking code can been found here: [tracking.js](https://github.com/lephleg/stalker/blob/master/src/storage/app/tracking.js).

### Endpoints

<table>
	<tr>
        <th>Method</th>
		<th width="400px">URI</th>
		<th>Description</th>
 	</tr>
 	<tr>
        <td><b>GET</b></td>
   		<td><pre>/sites</pre></td>
        <td>Returns all the registered websites.</td>
 	</tr>
	<tr>
  		<td><b>GET</b></td>
   		<td><pre>/sites/{id}/tracking-code</pre></td>
        <td>Serves the JavaScript tracking code after checking on website details.</td>
 	</tr>
	<tr>
  		<td><b>POST</b></td>
   		<td><pre>/sites/{id}</pre></td>
        <td>Used by tracking code to post tracking data in a predefined format.</td>
 	</tr>
</table>

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

    MAILCHIMP_API_KEY=
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


## TODO List

* Add UI to register new websites, fetch initial JavaScript snippet for your site and present analytics about unique visitors, page views, browser usage, etc, based on Stalker's data collected.