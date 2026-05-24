# Deployment & Operations

## Local setup (without Docker)

Typical steps:

1. Install PHP dependencies.
2. Configure `.env.local`.
3. Build the frontend.
4. Prepare the database.
5. Start the application via a web server or Symfony CLI.

### Install frontend dependencies

```bash
pnpm install
```

### Build frontend assets

```bash
pnpm build
```

## Scheduled tasks

- Data aggregation runs via the `aggregate` console command.
- A ready-made crontab line is printed by `register-crontab`.
- User management (migration between file and DB storage) is handled by dedicated commands.

## Example crontab entry

```cron
*/15 * * * * cd /var/www/pinboard/current && php bin/console aggregate --no-interaction >> /var/log/pinboard/aggregate.log 2>&1
```

This runs aggregation every 15 minutes and writes output to a log file. Adjust paths to match your environment.

## Data visibility timeline

After you connect a PHP application and start sending Pinba data, information appears in Pinboard in two stages:

| Time elapsed | What becomes visible |
|---|---|
| ~15 min (1st aggregation) | Server appears on the **main dashboard** (`/`) with request counts and timing |
| ~2.5 h (10th aggregation) | Server appears in the **navigation menu** |

The navigation menu filters out servers with fewer than 10 aggregation records to avoid showing one-off or test traffic. During the warm-up period you can reach a server's detail pages directly from the main dashboard.

To trigger an aggregation immediately (useful during initial setup):

```bash
php bin/console aggregate
```

## Updating from version 1.x

If you have an existing Pinboard 1.x installation and want to upgrade to 2.x:

1. Back up the database.
2. Switch to the new branch / release tag.
3. Update PHP and Node dependencies:

   ```bash
   composer install
   pnpm install && pnpm build
   ```

4. Copy your old `config/parameters.yml` to the new project — authentication settings are preserved.
5. Run migrations:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

   For existing installs the migration `Version20260309173000` will detect whether the PINBA virtual tables
   need to be recreated with the correct engine and skip automatically if they are already correct.

6. Verify the app by logging in and checking the data overview.

## Frontend

After changes in `assets/` rebuild the frontend to synchronise sources with `public/build`:

```bash
pnpm build
```

## Docker

If using the Docker environment, refer to `Makefile` and [docs/docker.md](docker.md) for available commands.
