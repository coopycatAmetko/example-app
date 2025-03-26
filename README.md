Система комментариев на Laravel + Vue / Comment System (Laravel + Vue)
🌟 Особенности / Features
    Древовидные комментарии к постам и другим комментариям
    Загрузка изображений с автоматическим ресайзом через очереди
    Кэширование комментариев для быстрой загрузки
    Вебсокеты (Laravel Echo) для实时 обновлений
    Капча (GD Library) для защиты от спама
    
    Tree comments on posts and other comments
    Image uploading with automatic resizing through queues
    Comment caching for fast loading
    Websockets (Laravel Echo) for updates
    Captcha (GD Library) for spam protection
    
🛠 Установка / Installation
    стянуть с гита проект
    создать папку /public/uploads
    создать фай .env можно скопировать содержимое с example-env и заполнить свои значения
    прописать команду composer install
    прописать команду php artisan key:generate
    sudo find -type d -exec chmod 775 {} \;
    sudo find -type f -exec chmod 664 {} \;
    пробросить симлинку папки public в папку www
    php artisan migrate
    npm install
    npm run build
    запустить сидер для создания постов php8.1 artisan db:seed --class="Database\\Seeders\\PostSeeder"
    если не будет работать капча установить gd - apt-get -y install php5-gd
    настроить pusher в .env и bootsrap.js
    обновить кеш конфигов

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
