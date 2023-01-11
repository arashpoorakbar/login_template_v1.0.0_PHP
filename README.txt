Login Template (Version 1.0.0)

This is a login template made by vanilla PHP. It provides a template for registering users, and signing in and provides two types of dashboards for normal login and admin login.
All pages could be substituted with an alternative front-end application.
However, The existing admin dashboard provides the possibility for each admin to edit, delete, and add users with an access level less than their own. 
This could be used for implementing any web application which needs users and admins. Moreover, the code is formed and commented on in a way that can be used for educational purposes and understanding the different features.
This version (1.0.0) uses a MySQL database for storing user information. 

Each registered email, while it is not activated, will receive a JSON web token within a link to be verified.

The admin route and dashboard work as one-time permission to update the users' table with a limited time of validity of the session. This means that after each modification or expiration of the session, the admin must log in again. Each time admin will receive an OTP in their email to load the users' table and update it to secure the users' route. 



1. loginconfig.json file
For the application to work, some parameters must be passed into it. This could be done through the loginconfig.json file in the root directory. The loginconfig.json file looks like this:


{
    "version" : "1.0.0",
    "MYSQLConnection": {
                    "host"     : "Database URL",
                    "user"     : "username for database",
                    "password" : "password",
                    "database" : "Database name"
                },
    "users_table" : "name of the users' table",
    "email_config" :    {
                        "service"  : "The service provider, eg. Smtp.gmail.com",
                        "user" : "email address",
                        "pass" : "email password"
                        
    },
    "emailVerification" : {
        "keyForToken" : "A key to secure the authentication tokens for verifying email",
        "tokenMaxAgeSecond" : "maximum age of tokens for verifying emails in seconds",
        "timeForResendSecond" : "time to be elapsed before resending a new verification email"
    },
    "admin_access": {
        "keyForToken": "a key for securing the tokens passed to admin",
        "tokenMaxAgeSecond": "maximum age for the admin tokens in seconds"
    }
}


The parameters for the MySQL database are clear. "multipleStatements" should always be true. This won't affect the code and its performance, it is a reminder that the admin's queries from dashboard could contain multiple queries which could be susceptible to injection attacks.

The email which is going to be used as the transporter must be declared with parameters. However, it is good to pay attention that, usually in the case of Gmail and Yahoo, one must set the two-step authentication, and later create a password to be used with other applications which can be entered here.

The maximum age of the tokens used for email validation and admin access for table modification can each be separately defined.

The time which needs to be elapsed before a new verification email could be sent is set by "timeForResendSecond".


2. MYSQL Database

For the app to function, it must be able to connect to a MYSQL server with the parameters passed in the loginconfig.json. The database must have a table with the name passes as "users_table" in the loginconfig.json.

The table must have columns as:

id VARCHAR(255) PRIMARY KEY and NOT NULL and UNIQUE index
username VARCHAR(255) UNIQUE index
password VARCHAR(255)
name VARCHAR(255)
lastname VARCHAR(255)
email VARCHAR(255) UNIQUE index
accessLevel INT DEFAULT "1"
registrationDate DATETIME
isAdmin TINYINT(1)
isActivated TINYINT(1)
timeEmailSent INT


And obviously, the user whose credentials are passed to the loginconfig.json must have all the privileges to work with the users' table.

And in order to have a top-level admin in the table, one can insert it into the table with an access level set to 10. Pay attention to setting the id value as UUID() to get a UUID for your top-level admin.


3. Routes
The application has a '/' route that redirects to the login page. This could be replaced with a nicely designed landing page.

'/login' simply takes username and password and in case of wrong credentials gives the proper message.
If the credentials are verified and the user's email is not activated, it will redirect to '/verification'.
If the credentials are verified it will be redirected to '/dashboard'.
In case the user is an admin an OTP will be sent to their email and a JWT in the response as a cookie for authentication.

'/signup' gives the possibility to register. It checks username and email not to be repetitive. In case of successful registration, an email will be sent to the user's email for verifying their email.

'/dashboard' is only accessible to the users who have verified themselves with credentials.
Users who are not admins can later get their dashboard through another application. The default view just shows their id and a button to log out.
Admin dashboard by default is a page where it is possible to load the users' table with the users whose access level is less than the admin's, by the OTP sent to the admin's email. These data could be modified by the admin using the same OTP. However, it could be used only once. After each usage, it will be expired and the session will be destroyed.

Admin dashboard could be replaced by another AI or application. In that case, it is better that the cookie "authToken" be replaced in the header as "Authorization" or "X-Auth-Token" to better comply with REST API conventions. For this, a small modification must be done in the users.js as well.


4. Rewriting URLs
It is possible to rewrite URLs within the web server to avoid .php in the end of URLs.
That could be done within the settings of the web server. In case of Apache Web Server, apart from modifying the main httpd.conf file, one can have a .htaccess in the root directory.
In that case, in the main config file the following commands must be set for that directory:
AllowOverride all
Require all granted

And the .htaccess file must contain a rewrite formula like this:


Options +SymLinksIfOwnerMatch

#These rewrite rules could be easily substituted with a RegEx.
#However, for clarity each url is giver a seperate rewrite rule here. 


RewriteEngine on 
RewriteRule ^index$ index.php [NC]

RewriteEngine on 
RewriteRule ^login$ login.php [NC]

RewriteEngine on 
RewriteRule ^signup$ signup.php [NC]

RewriteEngine on 
RewriteRule ^dashboard$ dashboard.php [NC]

RewriteEngine on 
RewriteRule ^admindashboard$ admindashboard.php [NC]

RewriteEngine on 
RewriteRule ^users\/(.*) users.php/$1 [NC]

RewriteEngine on 
RewriteRule ^verification$ verification.php [NC]

This is for the simplicity of presentation. However, using RegEx these commands could be embedded into each other.





For any suggestions or questions feel free to contact: arash.poorakbar@gmail.com

