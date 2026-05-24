# Configuration

## Files

- `.env` — default values committed to the repository.
- `.env.local` — local overrides and secrets; never committed.
- `.env.test` — test environment settings.

## Variables

Minimum required to run the application:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `MAILER_DSN`

Commonly configured application parameters:

| Variable | Default | Description |
|---|---|---|
| `APP_BASE_URL` | `/` | URL prefix if Pinboard is mounted under a sub-path |
| `APP_PAGINATION_ROW_PER_PAGE` | `50` | Rows per page in data tables |
| `APP_RECORDS_LIFETIME` | `P1M` | How long to keep raw data (ISO 8601 duration) |
| `APP_AGGREGATION_PERIOD` | `PT15M` | Aggregation window size |
| `APP_LOGGING_LONG_REQUEST_TIME_GLOBAL` | `1.5` | Slow-request threshold in seconds (site-wide) |
| `APP_LOGGING_LONG_REQUEST_TIME_MAP` | `{}` | Per-server overrides as JSON object |
| `APP_LOGGING_HEAVY_REQUEST_GLOBAL` | `30000` | Heavy-request memory threshold in KB |
| `APP_LOGGING_HEAVY_REQUEST_MAP` | `{}` | Per-server overrides as JSON object |
| `APP_LOGGING_HEAVY_CPU_REQUEST_GLOBAL` | `1` | Heavy-CPU threshold in seconds |
| `APP_LOGGING_HEAVY_CPU_REQUEST_MAP` | `{}` | Per-server overrides as JSON object |
| `APP_NOTIFICATION_ENABLE` | `0` | Set to `1` to enable email notifications |
| `APP_NOTIFICATION_SENDER` | `noreply@pinboard.local` | From address for notification emails |
| `APP_NOTIFICATION_GLOBAL_EMAIL` | _(empty)_ | Default recipient email |
| `APP_NOTIFICATION_IGNORE` | _(empty)_ | Comma-separated hostnames to exclude from alerts |
| `APP_NOTIFICATION_LIST_JSON` | `[]` | Per-server notification rules as JSON array |
| `APP_NOTIFICATION_REQ_TIME_BORDER_GLOBAL` | `1.5` | Percentile alert threshold in seconds |
| `APP_NOTIFICATION_REQ_TIME_BORDER_MAP` | `{}` | Per-server overrides as JSON object |
| `APP_AUTH_USER_SOURCE` | `file` | User storage backend: `file` or `db` |
| `APP_AUTH_USERS_FILE` | `config/parameters.yml` | Path to the file-based user list (relative to project root or absolute) |

## Authentication

Pinboard supports two user storage backends — select one via `APP_AUTH_USER_SOURCE`.

### File-based auth (default)

Users are stored in the YAML file pointed to by `APP_AUTH_USERS_FILE` (default: `config/parameters.yml`).

Example `config/parameters.yml`:

```yaml
secure:
    enable: true
    users:
        admin:
            email: admin@example.com
            password: <hashed-password>   # use: php bin/console add-user
            roles:
                - ROLE_ADMIN
                - ROLE_USER
            hosts: '.*'                   # regex — limits which servers this user sees
```

Use the `add-user` console command to create or update users (it hashes the password automatically):

```bash
php bin/console add-user admin@example.com yourpassword ROLE_ADMIN
```

### Database auth

Set `APP_AUTH_USER_SOURCE=db`. Users are stored in the `user` table (created by migrations).

Migrate existing file users to the database:

```bash
php bin/console users:migrate-file-to-db
```

Migrate back:

```bash
php bin/console users:migrate-db-to-file
```

## Email notifications

Set `MAILER_DSN` to a valid Symfony Mailer DSN, for example:

```env
MAILER_DSN=smtp://user:password@smtp.example.com:587
```

Then enable notifications:

```env
APP_NOTIFICATION_ENABLE=1
APP_NOTIFICATION_GLOBAL_EMAIL=alerts@example.com
APP_NOTIFICATION_SENDER=noreply@pinboard.example.com
```

See [Symfony Mailer documentation](https://symfony.com/doc/current/mailer.html) for all supported DSN formats (SMTP, sendmail, Amazon SES, Mailgun, etc.).
