# Wabwar Vault

An all-in-one internal portal for Wabwar Software House, plus a customer
support-chat product — one Laravel app, one MySQL database.

It does five things:

1. **Team work-log** — every member logs their day (completed / delivered /
   critical / pending / saved passwords & links). No password for members;
   they pick their name on the start screen.
2. **Owner dashboard** — see everyone's day, pending work, and productivity
   analysis; assign critical tasks; message members directly.
3. **Employee management** — add / edit / archive / restore. **Deleting an
   employee archives them — their log is never erased.**
4. **Device monitoring** — file/USB events, active-app time, periodic
   screenshots, site/app blocking, and safe whitelisted remote actions, via a
   consent-based Windows agent.
5. **Support chat** — one inbox for both a web widget and a desktop (WinForms)
   control, with a per-customer script generator.

## Stack

- Laravel 11 (PHP 8.2)
- MySQL
- Deployed on Railway with GitHub auto-deploy
- Realtime by polling (simple and reliable on this stack)
- Windows agent + WinForms chat control are separate distributables (see
  `agent/` and `winforms/`)

---

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
# point .env DB_* at a local MySQL, then:
php artisan migrate --seed
php artisan serve
```

- Portal login: `http://localhost:8000/login`
  - Super owner: `admin@wabwar.com` / `password`
  - Head: `tariq@wabwar.com` / `password`
- Member start (no login): `http://localhost:8000/`

---

## Deploy: GitHub → Railway (auto-deploy)

### 1. Push to GitHub

```bash
git init
git add .
git commit -m "Wabwar Vault"
git branch -M main
git remote add origin https://github.com/<you>/wabwar-vault.git
git push -u origin main
```

### 2. Create the Railway project

1. On [railway.app](https://railway.app) → **New Project → Deploy from GitHub
   repo** → pick `wabwar-vault`.
2. Add a **MySQL** database plugin to the same project (**New → Database →
   MySQL**). Railway injects `MYSQL_URL`.
3. In the web service **Variables**, set:
   - `APP_KEY` — generate one locally with `php artisan key:generate --show`
     and paste it (value starts with `base64:`).
   - `APP_URL` — your Railway URL, e.g. `https://wabwar-vault.up.railway.app`
   - `CHAT_BASE_URL` — same as `APP_URL`
   - `DB_URL` — `${{MySQL.MYSQL_URL}}` (reference the MySQL plugin)
   - `APP_ENV=production`, `APP_DEBUG=false`

`nixpacks.toml` installs PHP 8.2 + extensions and runs
`composer install`. `railway.json` runs `php artisan migrate --force` and
serves `public/` on `$PORT` at boot. The `/up` health check confirms it's live.

### 3. First deploy

Railway builds and deploys on push. On first boot, migrations run
automatically. To seed demo data once:

```bash
# from the Railway service shell (or `railway run`):
php artisan db:seed --force
```

### 4. Auto-deploy on every push

Once the repo is connected, Railway redeploys on every push to `main`.
`.github/workflows/deploy.yml` also runs a CI sanity check (installs deps,
loads routes, lints the entry point). If you prefer CLI deploys, add
`RAILWAY_TOKEN` and `RAILWAY_SERVICE` as repo secrets — otherwise the deploy
step is skipped and Railway's own GitHub integration handles it.

---

## Support chat: give a customer a widget

1. Portal → **Widgets & scripts** → add the customer (web / desktop / both).
2. Copy the generated snippet:
   - **Web** — a `<script src=".../widget.js" data-key="...">` tag they paste
     before `</body>`.
   - **Desktop** — add `winforms/SupportChatPanel.cs` to their WinForms app and
     drop the panel on a form with the generated `ClientKey`.
3. Both land in the same **Support inbox**. Customer context (page, browser,
   OS, city) is captured automatically. Screen share happens only when the
   customer clicks to share.

---

## Device agent

See `agent/README.md`. Key point: it is **consent-based** — visible tray icon,
first-run consent, uninstallable, and only the 10 whitelisted safe actions.
There is no arbitrary-command capability anywhere in the server or the agent,
by design.

---

## Project layout

```
app/Http/Controllers/Portal   portal screens
app/Http/Controllers/Api      chat + agent APIs
app/Models                    Eloquent models
app/Http/Middleware           tier + agent auth guards
database/migrations           full schema
database/seeders              demo data
resources/views/portal        Blade UI (matches the approved design)
public/widget.js              web support-chat widget
winforms/SupportChatPanel.cs  desktop support-chat control
agent/                        Windows monitoring agent reference
routes/web.php                portal + member (no-login) routes
routes/api.php                chat + agent routes
railway.json, nixpacks.toml   Railway deploy config
.github/workflows/deploy.yml  CI + deploy
```
