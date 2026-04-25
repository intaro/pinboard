# Pinboard (Symfony 8)

Админка для Pinba на Symfony 8.

Проект использует:
- PHP 8.4 или новее;
- современный Node.js для сборки фронтенда;
- `.env` и `.env.local` для конфигурации;
- Symfony Console для служебных задач;
- Symfony Encore для сборки фронтенда;
- `pnpm` для зависимостей фронтенда.

Базовые современные соглашения проекта описаны в [docs/standards.md](docs/standards.md).

## Быстрый старт

1. Установить зависимости:
   - `composer install`
   - `pnpm install`
2. Подготовить локальную конфигурацию в `.env.local`.
3. Собрать фронтенд:
   - `pnpm build`
4. Подготовить базу данных:
   - `php bin/console doctrine:migrations:migrate`
   - `php bin/console doctrine:fixtures:load`
5. Создать пользователя для входа:
   - `php bin/console add-user admin@admin.com admin ROLE_USER`
6. Запустить приложение через ваш веб-сервер или Symfony CLI.

## Основные команды

- `php bin/console aggregate`
- `php bin/console register-crontab`
- `php bin/console add-user <email> <password> [roles_csv] [hosts_regexp]`
- `php bin/console users:migrate-file-to-db`
- `php bin/console users:migrate-db-to-file`

## Документация

- [Конфигурация](docs/configuration.md)
- [Проверка и тестирование](docs/testing.md)
- [Запуск и эксплуатация](docs/deployment.md)
- [Сводка по документации](docs/README.md)
