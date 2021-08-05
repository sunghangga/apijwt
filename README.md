# Docker Installation
Apijwt is API to handle simple Meal Delivery using JWT Authentication. If you already have docker on your machine, you'll just have to clone this repository.

# 1. Clone repository

```
git clone https://github.com/sunghangga/apijwt.git
```

# 2. Create new user mysql name “apijwt”, following this command:

```
CREATE USER 'apijwt'@'localhost' IDENTIFIED BY 'apijwt';
GRANT ALL PRIVILEGES ON * . * TO 'apijwt'@'localhost';
```

# 3. Build and running docker with following command:

```
docker-compose build && docker-compose up -d
```

If you want to check container is running or not, can use this command:

```
docker container ls
```

# 4. Create .env file with copy .env.example in folder “src” and change the connection:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=db_apijwt
DB_USERNAME=apijwt
DB_PASSWORD=apijwt
```

# 5. Do composer install, generated key, migrate and seeder with this command:

```
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate:fresh --seed
```

Docker project can be access in http://localhost:8088. If want connect to mysql container, use this setting:

```
Host: localhost
Port: 4306
Username: apijwt
Password: apijwt
```

Download [ERD.drawio](https://github.com/sunghangga/apijwt/blob/master/ERD.drawio) file and run to [draw.io](https://draw.io/) site to see ERD for this system.

Here for the [documentation](https://github.com/sunghangga/apijwt/blob/master/documentation.docx).
