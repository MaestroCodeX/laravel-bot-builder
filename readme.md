1 - in the root project directory rename ```.env.example``` file to ```.env```

2 - run below command in the project root directory

        php artisan key:generate

3 - fill below parameter for app url in the ```.env ``` file

        APP_URL=your https url example : https://doman.com/
                
4 - fill mysql database config in the ```.env``` file with below parameter

        DB_DATABASE=
        DB_USERNAME=
        DB_PASSWORD=

                 
5 - fill below parameter in the ```.env``` file

        TELEGRAM_BOT_TOKEN=your master bot token from @botFather
        TELEGRAM_BOT_ID=your master bot id from master bot token
        WEBHOOK_URL=your https webhook url  example : https://doman.com/

6 - after fill the ```.env``` file run below command from project root directory

        php artisan config:cache

7 - run below command in the project root directory to migrate database tables

        php artisan migrate
                
8 - and then run below command to set webHook url for master bot

        php artisan bot:config        
        
        
        
                 
