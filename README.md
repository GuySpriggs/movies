# Movies Website

### Local dev requirements:
1. PHP: 8.2.0
2. MYSQL: 5.7.39
3. Composer version: 2.4.4

### Localhost setup:
1. Clone the repo (Link provided below)
2. Setup your database (Import the SQL provided)
3. Configure your settings.php as per your Mysql setup.
4. Composer install to get all the packaged.
5. Run drush cim and drush cr.

### Key features:
1. A styled view on the homepage with various filters and sorting options. (Styled through SASS)
2. A placeable block that accepts an image, title and body which can be placed as a hero anywhere on the site.
3. A form that processes movies through batch processing which imports them into the site (This is done through two services CreateMovieService and ImportMovieService.)

To add movies follow these steps:
1. Login as an admin user using drush uli, you will get an output like this: http://default/user/reset/1/1686160038/QonZHP3Z7dwHywPCo7fANT1m0Y2Pvd3shZW_5BMJ5sc/login
   Append from /user/reset/... to your site's url and it will log you in as admin.
2. Go to /admin/config/system/api-settings and fill in the movie search parameter (Eg: Dog) and the year (Eg 2018). - I have added my API key for testing
3. Once the form has been completed, the new movies should pull through to the homepage of the site.

### Possible future versions:
1. Improve mobile responsiveness.
2. Various styling fixes.
3. Choosing which movies to import from the list.
4. Choosing how many movies to import at a time.
5. Importing more data from the movies (More fields and ratings)
