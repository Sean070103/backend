# Deploy Laravel API on Render

## Option A: Blueprint (recommended)

1. Push this repo to GitHub and connect it to Render.
2. In Render: **New** → **Blueprint** → connect the repo.
3. Render will create a **Web Service** (Docker) and a **PostgreSQL** database and link them.
4. In the Web Service **Environment** tab, set:
   - **APP_URL** = your service URL (e.g. `https://laravel-api-xxxx.onrender.com`)
   - **APP_KEY** = run locally `php artisan key:generate --show` and paste the value (or use Render’s “Generate” and ensure it’s a valid Laravel key)
5. Redeploy. After deploy, run migrations (they run automatically in `docker/render-start.sh`). To seed: **Shell** tab → `php artisan db:seed`.

## Option B: Manual setup

1. **New** → **PostgreSQL**; create a database and note the **Internal Database URL**.
2. **New** → **Web Service**; connect the repo, choose **Docker**.
3. Add environment variables:
   - `APP_ENV` = production  
   - `APP_DEBUG` = false  
   - `APP_URL` = https://your-service-name.onrender.com  
   - `APP_KEY` = (from `php artisan key:generate --show`)  
   - `DB_CONNECTION` = pgsql  
   - `DATABASE_URL` = (Internal Database URL from step 1)  
   - `CORS_ALLOWED_ORIGINS` = https://task-sand-sigma.vercel.app  
4. Deploy. Migrations run on start. To seed: open **Shell** and run `php artisan db:seed`.

## Frontend (Vercel)

Set **NEXT_PUBLIC_API_URL** to your Render API URL (e.g. `https://laravel-api-xxxx.onrender.com`), then redeploy.

## Optional: Typesense

If you use search, set `TYPESENSE_ENABLED=true`, `TYPESENSE_HOST`, and `TYPESENSE_API_KEY` in the Web Service env, then run `php artisan typesense:reindex` from the Shell after seeding.
