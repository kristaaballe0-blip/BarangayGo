# BarangayGo

## Source organization

| Path                   | Purpose                                                                             |
| ---------------------- | ----------------------------------------------------------------------------------- |
| `index.php`            | Front controller: validate requests, dispatch actions, and select the active module |
| `db.php`               | Shared sessions, database connection, transaction helper, and initial data          |
| `modules/registry.php` | Allowlisted module routes, navigation, permissions, and action handlers             |
| `modules/auth/`        | Login, logout, and resident account registration                                    |
| `modules/dashboard/`   | Summary statistics and recent requests                                              |
| `modules/residents/`   | Resident creation, editing, deletion, and directory                                 |
| `modules/requests/`    | Document requests, status updates, filtered data, and shared request table          |
| `modules/documents/`   | Document types, requirements, fees, and availability                                |
| `modules/payments/`    | Payment recording and ledger                                                        |
| `modules/reports/`     | Report view and Excel export                                                        |
| `modules/profile/`     | Account details and password changes                                                |
| `modules/logs/`        | Staff activity log                                                                  |
| `includes/helpers.php` | Shared validation, staff authorization, and form fields                             |
| `views/layout.php`     | Shared navigation and application shell                                             |
| `assets/`              | Styles, responsive layouts, print rules, and form interactions                      |
| `database/`            | Connection settings, schema, and command-line setup                                 |
| `storage/`             | Private sessions and retained SQLite backup (ignored by Git)                        |
| `tools/format.mjs`     | Consistent source formatting                                                        |

For development, run `npm ci`, then `npm run format` to organize source formatting or `npm run format:check` to check it. Node.js and these formatter packages are development tools; the deployed website still runs on PHP alone.

PHP 8.1+ community services application. Uses MySQL / MariaDB with PDO MySQL and InnoDB transactions. No Composer packages required.

## Run

The application now targets the hosting database **if0_43266472_BarangayGo** on **sql309.infinityfree.com:3306**, using username **if0_43266472**.

Connection settings are in `database/config.php`. The database password must be supplied through the server environment variable `BARANGAY_DB_PASSWORD`; it is not saved in the source files. Other connection overrides are `BARANGAY_DB_HOST`, `BARANGAY_DB_PORT`, `BARANGAY_DB_NAME`, and `BARANGAY_DB_USER`.

For hosting: open the database's phpMyAdmin from the hosting control panel, select **if0_43266472_BarangayGo**, and import `database/schema.sql`. This file creates all seven InnoDB tables without trying to create or select a different database. Upload the application files, configure the password on the server, then open the hosted website to initialize the demo accounts. This schema import does not transfer local records; export those separately from local phpMyAdmin if needed.

For local XAMPP use, `database/config.local.php` overrides the host to `127.0.0.1`, port to `3307`, name to `barangaygo`, user to `root`, and password to an empty string. This local override is ignored by Git; **do not upload it to hosting**. Without that file, the hosting defaults apply. Server environment variables take precedence over both files. The local database remains available at http://localhost/phpmyadmin/index.php?route=/database/structure&db=barangaygo .

For a fresh installation run `C:\xampp\php\php.exe database/setup.php` from this folder, or import `database/schema.sql` through phpMyAdmin. The setup command also migrates the former SQLite database if present and the destination tables are empty. Existing MySQL data is preserved. Open the app to initialize demo accounts if no accounts have been migrated. The original `storage/barangay.sqlite` is retained as a backup and is no longer used by the application.

Initial local demo accounts:

| Role     | Email                   | Password           |
| -------- | ----------------------- | ------------------ |
| Staff    | admin@barangay.local    | BarangayAdmin!2026 |
| Resident | resident@barangay.local | Resident!2026      |

Change both demo passwords through My profile before deployment. Use HTTPS and a dedicated database user in production. The storage and database folders must remain inaccessible over HTTP; their Apache `.htaccess` files deny access. For other servers configure equivalent deny rules. Back up MySQL through phpMyAdmin Export or mysqldump.

## Included

Resident CRUD with linked-record protection; password-hashed login; staff/resident authorization; 30-minute idle sessions; session regeneration; CSRF tokens; login throttling; dashboard statistics; name/type/status filtering; sequential Pending → Ready for Pickup → Claimed request workflow; document types, fees and requirements; full-payment tracking with duplicate protection; profile updates; activity log; Excel-compatible XML workbook export and browser Print / Save as PDF reports. Resident accounts see only their own requests. Staff administer records and payments. The Create account link on the login page registers a resident account and its linked resident record. Public registration always assigns the resident role; staff accounts require administrator provisioning.

Fees are stored in integer centavos and captured on each request. Claiming a paid document requires a recorded payment. Daily dashboard totals use Philippine time; database event timestamps and table timestamps use UTC.

## Transactions and sessions

`db.php` configures secure session options, regeneration, timeout, and the transaction helper. The action files in `modules/residents/`, `modules/requests/`, and `modules/payments/` explicitly identify the three required database transactions: resident write + audit; request creation + captured fee + audit; validated payment + audit. Resident registration in `modules/auth/` also creates its profile, account, and audit entry in one transaction. Status changes, type edits, account changes, and deletions also use transactions. Exceptions roll back all writes in each operation.

Reports respect the name/type and status filters. Excel exports contain text cells to prevent formula execution. PDF output uses the browser print dialog; select Save as PDF. Fonts use Google Fonts with local Arial fallbacks.

## Module request flow

`index.php` loads the shared bootstrap and the module registry. A POST action must pass CSRF validation before the matching allowlisted handler is loaded. Each staff action also checks its authorization. The controller then loads the active module view inside the shared layout. Direct HTTP access to `modules/`, `includes/`, and `views/` is denied by their `.htaccess` files. Existing `?page=` URLs stay compatible; the authentication screen switches between `?auth=login` and `?auth=register`.
