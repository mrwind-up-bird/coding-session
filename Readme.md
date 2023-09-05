# 🐳 Docker + PHP 8.2 + MySQL + Nginx + Symfony 6.2

## Description

Coding Session for an application as Developer@Webnetz. This Repository runs a Docker Container with MySQL, PHP 8.2, Symfony 6.x and NGinX 

## Installation

1. Clone this Repo.
2. Create the file `./.docker/.env.nginx.local` using `./.docker/.env.nginx` as template. The value of the variable `NGINX_BACKEND_DOMAIN` is the `server_name` used in NGINX.
3. Go inside folder `./docker` and run `docker compose up -d` to start containers.
4. You should work inside the `php` container. This project is configured to work with [Remote Container](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers) extension for Visual Studio Code, so you could run `Reopen in container` command after open the project. 
5. Inside the `php` container, run `composer install` to install dependencies from `/var/www/symfony` folder.
6. Run `npm install` and `npm run watch` to build the Encore Assets
7. Use the following value for the DATABASE_URL environment variable:
```
DATABASE_URL=mysql://app_user:helloworld@db:3306/app_db?serverVersion=8.0.33
```