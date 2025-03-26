Comment System (Laravel + Vue)
Tree comments on posts and other comments
Image uploading with automatic resizing through queues
Comment caching for fast loading
Websockets (Laravel Echo) for updates
Captcha (GD Library) for spam protection
    
Установка / Installation
pull the project from the git
create the /public/uploads folder
create an .env file, you can copy the contents from example-env and fill in your own values.
run the composer install command
command php artisan key:generate
sudo find -type d -exec chmod 775 {} \;
sudo find -type f -exec chmod 664 {} \;
migrate the public folder symlink to the www folder
php artisan migrate
npm install
npm run build
start the post feeder php8.1 artisan db:seed --class=“Database\\Seeders\\PostSeeder”.
if captcha won't work install gd - apt-get -y install php5-gd
customize pusher in .env and bootsrap.js
update config cache
