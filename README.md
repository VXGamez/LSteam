# PW II - F2
> Hem fet servir Docker pel nostre Local environment

## Instalació

1. Clonar aquest repositori emprant la comanda `git clone`

## Instruccions

1. Després de clonar el projecte, desplaça't en un terminal a la carpeta amb la comanda `cd /ubicacio/de/carpeta`.
2. Per iniciar el local environment emprar la comanda `docker-compose up -d` en el terminal.

En aquest punt emprant la comanda `docker-compose ps` s'haurien de veure un total de 4 contenidors executant-se:

```
       Name                     Command               State                 Ports              
-----------------------------------------------------------------------------------------------
pw_local_env-admin   entrypoint.sh docker-php-e ...   Up      0.0.0.0:8080->8080/tcp           
pw_local_env-db      docker-entrypoint.sh mysqld      Up      0.0.0.0:3330->3306/tcp, 33060/tcp
pw_local_env-nginx   /docker-entrypoint.sh ngin ...   Up      0.0.0.0:8030->80/tcp             
pw_local_env-php     docker-php-entrypoint php-fpm    Up      9000/tcp, 0.0.0.0:9030->9001/tcp
```

**Nota:** Tot i que la comanda realitzada anteriorment hauria d'haver configurat l'entorn adequadament, recomanem realitzar la comanda `composer update` al CLI de php, accessible desde docker.

Arribats a aquest punt, i amb els contenidors iniciats, es podrà visitar el web a la següent pàgina: [http://localhost:8030/](http://localhost:8030/).

### Database

Per accedir a la pàgina admin de la base de dades visitar: [http://localhost:8080/](http://localhost:8080/) al navegador.

Els credencials per accedir-hi son:

**Usuari:** root

**Contrasenya:** admin

Un cop s'hi accedeixi, caldrà crear sinó es té ja una base de dades anomenada LSteam.

Es podrà fer servir la comanda:

```sql
DROP DATABASE IF EXISTS LSteam;
CREATE DATABASE LSteam;
USE LSteam;
```

Un cop creada la base de dades, executar-hi la següent comanda sql:

```sql
DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL DEFAULT '',
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `birthday` DATETIME NOT NULL,
    `phone` VARCHAR(255) NOT NULL DEFAULT '',
    `activated` BOOLEAN,
    `token` VARCHAR(255) NOT NULL DEFAULT '',
    `wallet` INTEGER NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `uuid` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Game`;
CREATE TABLE `Game` (
    `id` INT(11) unsigned NOT NULL,
    `storeID` INT(11) unsigned NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `thumb` VARCHAR(255) NOT NULL DEFAULT '',
    `dealRating` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `User-Game-Bought`;
CREATE TABLE `User-Game-Bought` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `gameID` INT(11) unsigned NOT NULL,
    `userID` INT(11) unsigned NOT NULL,
    `sellPrice` FLOAT DEFAULT 0,
    `dateBought` DATETIME NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`gameID`) REFERENCES Game(`id`),
    FOREIGN KEY(`userID`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `User-Game-Wishlist`;
CREATE TABLE `User-Game-Wishlist` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `sellPrice` FLOAT DEFAULT 0,
    `gameID` INT(11) unsigned NOT NULL,
    `userID` INT(11) unsigned NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`gameID`) REFERENCES Game(`id`),
    FOREIGN KEY(`userID`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Friend-User`;
CREATE TABLE `Friend-User` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `user1_id` INT(11) unsigned NOT NULL,
    `user2_id` INT(11) unsigned NOT NULL,
    `fecha` DATETIME NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`user1_id`) REFERENCES User(`id`),
    FOREIGN KEY(`user2_id`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Request`;
CREATE TABLE `Request` (
    `request_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_origen` INT(11) unsigned NOT NULL,
    `user_desti` INT(11) unsigned NOT NULL,
    `fecha` DATETIME NOT NULL,
    `pending` BOOLEAN,
    PRIMARY KEY(`request_id`),
    FOREIGN KEY(`user_origen`) REFERENCES User(`id`),
    FOREIGN KEY(`user_desti`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

```

Amb les taules **User**, **Game**, **User-Game-Bought**, **Friend-User**, **Request** i **User-Game-Wishlist**  creades ja es pot emprar la pàgina amb normalitat.