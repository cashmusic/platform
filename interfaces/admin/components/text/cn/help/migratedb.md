When you first install the platform the installer creates and configures a **SQLite** database for you. 
SQLite means we don't need to know any database server information and can get people up and playing 
with the platform quickly. But for performance it lags behind **MySQL** a fair bit as the database grows. 

If you're building things for production we recommend you migrate to a MySQL database, and we've 
included a migration tool so it'll be as easy as knowing your settings. Where and how you get your 
settings is different from host to host, but generally you'll find MySQL information in whatever 
host administration panel you log in to. 

You'll need: 

 - The MySQL server address
 - a username
 - a password
 - the name of an empty database you can use

Once you have everything just open up the CASH Admin, login, and hover over your email address under 
the main menu. You'll see a "System Settings" option. Click that, and in the left column you'll see 
your current database type, and if that's SQLite you'll also see a form that lets you put in your 
database server settings and migrate. 