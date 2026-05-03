# Docker: Pinboard + Pinba (MySQL 8)

Инструкция для быстрого локального запуска полного стека:

- `pinba_engine` в контейнере MySQL 8 (`xolegator/pinba-engine:*`)
- `pinboard` (Symfony 8 + PHP-FPM 8.5 + Nginx)
- фоновая агрегация (`aggregate` каждые 15 минут)

## 1. Подготовка

1. Убедиться, что Docker Engine и Docker Compose v2 установлены.
2. Проверить файл `docker/.env` и при необходимости поменять:
   - `PINBA_IMAGE` (например, `xolegator/pinba-engine:8.0`)
   - `MYSQL_ROOT_PASSWORD`
   - `DB_USER` / `DB_PASSWORD`
   - `NGINX_HOST_HTTP_PORT`

## 2. Запуск стека

Из корня проекта:

```bash
make up
```

Проверка состояния:

```bash
make dc_ps
make dc_logs
```

## 3. Первичная инициализация приложения

Когда контейнеры поднялись:

```bash
make db_migrate
make users_file_to_db
```

Если нужно создать пользователя вручную:

```bash
docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml \
  exec -u www-data php-fpm php bin/console add-user admin@admin.com admin ROLE_USER
```

## 4. Проверка работы

1. Открыть админку: `http://localhost:${NGINX_HOST_HTTP_PORT}`.
2. Проверить, что MySQL Pinba plugin активен:

```bash
docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml \
  exec mysql-pinba mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "SHOW PLUGINS LIKE 'pinba';"
```

3. Проверить логи агрегации:

```bash
docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml logs -f aggregate
```

4. Проверить таблицы Pinba:

```bash
docker compose --env-file ./docker/.env -f ./docker/docker-compose.yml \
  exec mysql-pinba mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D pinba -e "SHOW TABLES;"
```

## 5. Остановка

```bash
make down
```

С удалением тома БД:

```bash
make dc_down
```
