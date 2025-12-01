# Repository Guidelines

## Project Structure & Module Organization
- `backend/` – ThinkPHP 8 service with domain logic under `app/`, route maps in `route/`, and generated static pages in `html/`.
- `frontend/` – Vue 3 + Vite admin; author screens in `src/views`, share code in `src/components`, and publish bundles from `dist/`.
- `docs/` – Schema dumps, deployment notes, and feature guides; update the matching `.sql` or markdown file alongside code changes.
- `html/` – Published sitemaps and static HTML; regenerate via console jobs, never edit directly.

## Build, Test, and Development Commands
- Frontend: `cd frontend && npm install`; `npm run dev` serves `http://localhost:5173`, `npm run build` compiles production assets, and `npm run preview` hosts the bundle for smoke tests.
- API: `cd backend && composer install`; `php think run -p 8000` starts the local server, and run the existing static-publish commands before syncing output to `html/`.
- Database: Load snapshots with `mysql < docs/database_system.sql` (or the feature-specific dump) in staging before promoting to production.

## Coding Style & Naming Conventions
- Vue files use two-space indentation, PascalCase component filenames, and camelCase composables; scope component styles whenever possible.
- PHP follows PSR-12: four-space indentation, StudlyCaps classes, camelCase methods, and helper functions staged in `backend/extend/`.
- Keep configuration in the provided `.env.*` files and store secrets outside version control.

## Testing Guidelines
- Automated suites are not yet committed; document manual verification in each pull request and call out edge cases.
- Add backend coverage as PHPUnit cases under `backend/tests/*Test.php` (run with `vendor/bin/phpunit`) and front-end specs under `frontend/tests` once Vitest or Cypress harnesses are introduced.

## Commit & Pull Request Guidelines
- Use Conventional Commits (`feat:`, `fix:`, `chore:`) in the imperative mood—for example, `feat: support sitemap news feed`.
- Pull requests must explain scope, link issues, list validation steps, and attach UI evidence; include a short “Deployment Notes” block for migrations or operational changes.

## Environment & Configuration Tips
- Copy `backend/.env.example` to `.env` and align URLs with the port exposed by `php think run`.
- Maintain `.env.development` and `.env.production` in `frontend/`; rerun `composer dump-autoload` or `npm install` after changing manifests to avoid stale caches.
