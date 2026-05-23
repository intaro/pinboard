# Конфигурация

## Файлы

- `.env` - базовые значения по умолчанию.
- `.env.local` - локальные значения и секреты, не коммитится.
- `.env.test` - настройки тестового окружения.

## Переменные

Минимум для работы приложения:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `MAILER_DSN`

Часто настраиваемые прикладные параметры:

- `APP_BASE_URL`
- `APP_PAGINATION_ROW_PER_PAGE`
- `APP_RECORDS_LIFETIME`
- `APP_AGGREGATION_PERIOD`
- `APP_LOGGING_LONG_REQUEST_TIME_GLOBAL`
- `APP_LOGGING_LONG_REQUEST_TIME_MAP`
- `APP_LOGGING_HEAVY_REQUEST_GLOBAL`
- `APP_LOGGING_HEAVY_REQUEST_MAP`
- `APP_LOGGING_HEAVY_CPU_REQUEST_GLOBAL`
- `APP_LOGGING_HEAVY_CPU_REQUEST_MAP`
- `APP_NOTIFICATION_ENABLE`
- `APP_NOTIFICATION_SENDER`
- `APP_NOTIFICATION_GLOBAL_EMAIL`
- `APP_NOTIFICATION_IGNORE`
- `APP_NOTIFICATION_LIST_JSON`
- `APP_NOTIFICATION_REQ_TIME_BORDER_GLOBAL`
- `APP_NOTIFICATION_REQ_TIME_BORDER_MAP`
- `APP_AUTH_USER_SOURCE`
- `APP_AUTH_USERS_FILE`

## Аутентификация

Приложение поддерживает два источника пользователей:

- `file`
- `db`

Выбор задаётся через `APP_AUTH_USER_SOURCE`.
