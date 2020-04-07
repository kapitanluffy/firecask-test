### Laravel API

- Configure .env file
(`CLIENT_TEST_ID` and `CLIENT_TEST_SECRET` in `.env` are already prefilled)

- Run composer

> composer install

- Migrate & seed the database

> php artisan migrate --seed

- Run server

> php artisan serve

### Wordpress Site

- Add `postfetcher` in `wp-content/plugins`.
- Set the values under `Settings > Post Fetcher`. Use the `CLIENT_TEST` variables from `.env` file.
- A `Fetch Posts` button should show if settings are correct.
