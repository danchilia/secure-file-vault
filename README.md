# Secure File Vault

A Drupal 10 web application where registered users securely upload, organize, and
access their own private files — with a small custom module adding expiration
dates, per-user storage quotas, favorites, and file sharing between users.

## Features

- User registration and login (Drupal core)
- Secure upload of PDF, DOCX, images, ZIP, etc. to a **private** file field
- Files are private by default: only the owner, an admin, or a user the file has
  been explicitly shared with can view or download it
- File categories: Documents, Photos, Work, Personal
- Search files by name, filter by category (exposed View filters)
- Download files
- Delete files (owner only)
- Profile page showing per-user storage usage
- Admin dashboard listing every file across all users, with owner and storage
  usage columns
- File expiration dates (auto-unpublished by cron)
- Favorite files
- Share a file with another registered user

## Architecture

| Feature | Drupal mechanism |
|---|---|
| Users | Core `user` module, roles: `authenticated`, `vault_admin` |
| Files | Content type `vault_file` (see below), not a bare Media entity, so ownership/category/expiration all live on one node |
| Private files | `private://` scheme file field + `hook_file_download()` access gate |
| Privacy ("users only see their own") | `hook_node_access()` forbids `view` for everyone except the owner, an explicitly-shared user, or a role with `bypass node access` |
| Categories | A `list_string` field (`field_category`) with 4 fixed allowed values |
| Search / filter | Core Views exposed filters (no Search API/Solr needed) |
| My Files page | View `vault_my_files` at `/vault/my-files`, pre-filtered to the current user via a contextual filter defaulting to "current user" |
| Admin dashboard | View `vault_admin_files` at `/admin/content/vault-files`, permission-gated, shows every file + owner + storage usage |
| Roles & permissions | `authenticated` gets CRUD on own files only; `vault_admin` gets `bypass node access` + `administer users` for full oversight |
| REST | Core `jsonapi` module is enabled — every vault_file/user endpoint automatically respects the same access rules above |
| Custom module | `web/modules/custom/vault_extra` — see below |

### The `vault_file` content type

| Field | Type | Notes |
|---|---|---|
| Title | string | File's display name |
| `field_vault_document` | file (private scheme) | The actual upload; extensions: pdf, docx, doc, odt, txt, png, jpg, jpeg, gif, zip; 50MB max |
| `field_category` | list (text) | Documents / Photos / Work / Personal |
| `field_description` | text (long) | Optional notes |
| `field_expiration_date` | date | Added by the `vault_extra` module (see below) |
| Author (`uid`) | base field | The file owner |

### The `vault_extra` custom module

Everything under `web/modules/custom/vault_extra` implements the four bonus
requirements, on top of the base site:

- **File expiration dates** — ships `field_expiration_date` as module-owned
  config; `hook_cron()` unpublishes any `vault_file` node past its expiration
  date. `hook_preprocess_node()` adds an "Expired" / "Expires in N days" badge.
- **Storage usage per user** — `StorageCalculator` service sums the size of a
  user's uploaded files; shown as a progress bar on their profile page
  (`hook_user_view()`) and as a column in the admin dashboard View (a custom
  Views field plugin, `StorageUsageField`). A 100MB per-user quota is enforced
  at upload time via form validation (`vault_extra_node_form_quota_validate()`).
- **Favorites** — a `vault_extra_favorite` table, a toggle route/controller, a
  Views field plugin (`FavoriteToggleField`) for the "Favorite/Unfavorite" link
  on My Files, and a `/vault/favorites` listing page.
- **Sharing** — a `vault_extra_share` table, a `ShareFileForm` (user
  autocomplete, owner-only) at `/vault/share/{node}`, a `/vault/shared-with-me`
  listing page, and a `ShareLinkField` Views plugin. Sharing is enforced in both
  `hook_node_access()` (page access) and `hook_file_download()` (the actual
  file bytes) — a link alone would not be enough.

Custom permissions: `access vault my files page`, `access vault favorites page`,
`access vault shared page`, `access vault admin files page` (restricted),
`use vault file sharing`.

## Requirements

- PHP 8.2+
- Composer
- MySQL/MariaDB
- [Drush](https://www.drush.org/) (installed via Composer, see below)

## Local setup

```bash
# 1. Install dependencies
composer install

# 2. Create a database
mysql -u root -e "CREATE DATABASE secure_file_vault CHARACTER SET utf8mb4;"

# 3. Re-create web/sites/default/settings.php (not committed — see .gitignore)
cp web/sites/default/default.settings.php web/sites/default/settings.php
```

Then add these lines to the bottom of `web/sites/default/settings.php`
(adjust DB credentials for your environment):

```php
$databases['default']['default'] = [
  'database' => 'secure_file_vault',
  'username' => 'root',
  'password' => '',
  'host' => '127.0.0.1',
  'port' => 3306,
  'driver' => 'mysql',
  'prefix' => '',
];
$settings['config_sync_directory'] = '../config/sync';
$settings['file_private_path'] = $app_root . '/../private_files';
```

> The private path is set as an **absolute** path (`$app_root . '/../private_files'`)
> rather than a relative one — relative stream-wrapper paths resolve against the
> PHP process's current working directory, which differs between a normal web
> request (`cwd = web/`) and CLI tools like Drush (`cwd = project root`), causing
> the private directory to resolve to the wrong place under Drush.

```bash
# 4. Install the site and import the exported configuration
mkdir -p private_files
./vendor/bin/drush site:install minimal --existing-config \
  --account-name=admin --account-pass=CHANGE_ME -y

# 5. Serve it (for local testing)
php -S localhost:8080 -t web web/.ht.router.php
```

Visit `http://localhost:8080`, log in as `admin`, and go to **Content > Add
content > Vault File** to upload your first file, or `/vault/my-files` for the
user-facing listing.

## Repository layout

```
composer.json / composer.lock   Drupal core + Drush, pinned versions
config/sync/                    Exported site configuration (content type,
                                 fields, roles, Views) — the "recipe" for the site
web/modules/custom/vault_extra/ The custom module described above
web/sites/default/              default.settings.php + default.services.yml only;
                                 settings.php is generated locally, not committed
```

Drupal core, contributed modules, and `vendor/` are Composer-managed and are not
committed — run `composer install` to fetch them. This mirrors standard Drupal
project practice: the git history captures *your* code and configuration, not
upstream Drupal.

## Notes / local-dev caveats

- The default local DB credentials (`root` / no password) are appropriate only
  for local development against XAMPP's bundled MariaDB — replace them for any
  shared or production environment.
- The 100MB per-user storage quota in `StorageCalculator::QUOTA_BYTES` is a
  simple constant for this demo; a real deployment would likely make it
  configurable per role or account.
