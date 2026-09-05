# Genezenz Pharmacy — Hostinger Edition

Work-in-progress migration of Genezenz Pharmacy using PHP 8.2+, MySQL, semantic HTML, CSS and vanilla JavaScript. The existing PHP backend foundation has been brought into this repository. This checkpoint is not ready for production hosting or customer handover.

Read [`CURRENT-STATUS.md`](CURRENT-STATUS.md) first for the latest blockers and verification scope. Earlier migration and test reports describe the imported backend and do not establish full feature parity for this repository.

The original static website is preserved in `design-reference/`. The executable website is in `public_html/`, with private application code and configuration above it. Do not upload the entire repository to a public document root.

## Documentation

- Start with [`README-HOSTINGER.md`](README-HOSTINGER.md) for local setup and deployment.
- Use [`DEPLOYMENT-CHECKLIST.md`](DEPLOYMENT-CHECKLIST.md) before publishing.
- Review [`CLIENT-INPUT-REQUIRED.md`](CLIENT-INPUT-REQUIRED.md) for production credentials and business details.
- See [`TEST-REPORT.md`](TEST-REPORT.md) and [`PARITY-MATRIX.md`](PARITY-MATRIX.md) for verification and feature coverage.

## Security

The repository intentionally excludes `.env`, runtime logs and customer prescription uploads. Create production secrets from `.env.example`; never commit live credentials or uploaded health information.
