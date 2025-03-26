<strong>Comment System (Laravel + Vue)</strong>
<br>
Tree comments on posts and other comments
<br>
Image uploading with automatic resizing through queues
<br>
Comment caching for fast loading
<br>
Websockets (Laravel Echo) for updates
<br>
Captcha (GD Library) for spam protection
    
<strong>Installation</strong>
<br>
pull the project from the git
<br>
create the /public/uploads folder
<br>
create an .env file, you can copy the contents from example-env and fill in your own values.
<br>
run the composer install command
<br>
command php artisan key:generate
<br>
sudo find -type d -exec chmod 775 {} \;
<br>
sudo find -type f -exec chmod 664 {} \;
<br>
migrate the public folder symlink to the www folder
<br>
php artisan migrate
<br>
npm install
<br>
npm run build
<br>
start the post feeder php artisan db:seed --class=“Database\\Seeders\\PostSeeder”
<br>
if captcha won't work install gd - apt-get -y install php5-gd
<br>
customize pusher in .env and bootsrap.js
<br>
update config cache
