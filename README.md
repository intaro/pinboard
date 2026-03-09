# Pinboard (Symfony 6)

Проект переведён на конфигурацию через `.env` / `.env.local`.
Для совместимости со старой версией поддерживается файловое хранилище пользователей
в формате `config/parameters.yml` (`secure.users`), либо хранение в БД.

## Файлы настроек

- `.env` — шаблон/дефолты, можно хранить в git (без секретов).
- `.env.local` — локальные значения (секреты, пароли, хосты), **не коммитится**.
- `.env.test` — настройки для тестового окружения.

## Быстрый локальный запуск (без Docker)

1. Установить зависимости:
   - `composer install`
   - `pnpm install`
2. Подготовить локальный конфиг:
   - создать `.env.local` (или использовать уже подготовленный в вашей копии)
3. Собрать фронт:
   - `pnpm build`
4. Подготовить БД:
   - `php bin/console doctrine:migrations:migrate`
   - `php bin/console doctrine:fixtures:load`
5. Создать/обновить пользователя для входа:
   - `php bin/console add-user admin@admin.com admin ROLE_USER`
6. Запустить приложение (один из вариантов):
   - через ваш nginx + php-fpm
   - или `symfony server:start` (если установлен Symfony CLI)

## Обязательные переменные в `.env.local`

Минимум для старта:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `MAILER_DSN` (для локали можно `null://null`)

Приложенческие параметры (с дефолтами в `.env`, можно переопределять локально):

- `APP_BASE_URL`
- `APP_PAGINATION_ROW_PER_PAGE`
- `APP_RECORDS_LIFETIME`
- `APP_AGGREGATION_PERIOD`
- `APP_LOGGING_LONG_REQUEST_TIME_GLOBAL`
- `APP_LOGGING_LONG_REQUEST_TIME_MAP` (JSON)
- `APP_LOGGING_HEAVY_REQUEST_GLOBAL`
- `APP_LOGGING_HEAVY_REQUEST_MAP` (JSON)
- `APP_LOGGING_HEAVY_CPU_REQUEST_GLOBAL`
- `APP_LOGGING_HEAVY_CPU_REQUEST_MAP` (JSON)
- `APP_NOTIFICATION_ENABLE` (`0`/`1`)
- `APP_NOTIFICATION_SENDER`
- `APP_NOTIFICATION_GLOBAL_EMAIL`
- `APP_NOTIFICATION_IGNORE` (CSV)
- `APP_NOTIFICATION_LIST_JSON` (JSON)
- `APP_NOTIFICATION_REQ_TIME_BORDER_GLOBAL`
- `APP_NOTIFICATION_REQ_TIME_BORDER_MAP` (JSON)
- `APP_AUTH_USER_SOURCE` (`file` или `db`, по умолчанию `file`)
- `APP_AUTH_USERS_FILE` (путь до файла пользователей, по умолчанию `config/parameters.yml`)

## Полезные команды

- Агрегация: `php bin/console aggregate`
- Регистрация cron: `php bin/console register-crontab`
- Создание/обновление пользователя: `php bin/console add-user <email> <password> [roles_csv] [hosts_regexp]`
- Перенос пользователей файл -> БД: `php bin/console users:migrate-file-to-db`
- Перенос пользователей БД -> файл: `php bin/console users:migrate-db-to-file`

## Docker

Секция пока оставлена в прежнем виде:

- Если версия docker-compose < 3, в `Makefile` заменить `docker compose` на `docker-compose`.
- Базовый сценарий:
  - `make build`
  - `make up`
  - `make app_bash`
  - `php bin/console doctrine:migrations:execute --up DoctrineMigrations\\Version20231109083314`
  - `php bin/console doctrine:fixtures:load`
- Локальный URL: `http://127.0.0.1:888/`
