# FluxDrive — Beyond local limits.

A student-friendly cloud storage management app built with PHP 8+, MySQL, Bootstrap 5, Chart.js and Amazon S3.

## Features
- Email/password and Google OAuth
- Private Amazon S3 storage
- Upload multiple files with drag & drop
- Folder management
- Search
- Grid/list views
- Preview through short-lived S3 URLs
- Download
- Starred, recent and trash views
- Secure share links with expiry
- Dynamic storage analytics
- Activity log
- Light/dark theme
- Responsive UI
- CSRF, prepared statements, password hashing and ownership checks

## Stack
PHP 8+, MySQL 8+/MariaDB, AWS SDK for PHP, Google API Client, Bootstrap 5, Bootstrap Icons, Chart.js.

## Local setup (XAMPP)
1. Put the folder in `C:\xampp\htdocs\FluxDrive`.
2. Start Apache and MySQL.
3. Create/import the `database.sql` file in phpMyAdmin.
4. Install Composer and run `composer install` in the project folder.
5. Copy `.env.example` to `.env` and fill the values.
6. Open `http://localhost/FluxDrive/public/`.

### Composer
```bash
composer install
```

## AWS S3
Create a private bucket. Keep **Block Public Access** enabled and enable server-side encryption.

For local development you may use access keys in `.env`. For EC2 production, prefer an IAM instance role instead of long-lived keys.

Required:
- `AWS_REGION`
- `AWS_BUCKET_NAME`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`

Files are stored at:
`users/{user_id}/folders/{folder_id}/{random}-{safe_file_name}`

The application stores metadata in MySQL and the real bytes in S3.

## Google Login
1. Create a Google Cloud project.
2. Configure the OAuth consent screen.
3. Create a Web application OAuth client.
4. Add this redirect URI:
`http://localhost/FluxDrive/public/auth/google-callback.php`
5. Put the client ID, client secret and redirect URI in `.env`.

Production should use HTTPS. The client secret is server-side only.

## RDS + EC2
For production:
- Create an RDS MySQL database.
- Allow only the EC2 security group to reach port 3306.
- Launch EC2 with Apache/PHP and Composer.
- Attach an IAM role with the permissions in `aws-iam-policy.json`.
- Set the same app environment variables on EC2.
- Use HTTPS.

## CloudWatch
Send Apache/PHP/application logs to CloudWatch. Monitor authentication failures, upload failures, server errors and disk health.

## CloudFront
CloudFront is optional for static assets or public delivery. Private S3 files should continue to use access-controlled URLs.

## Environment
See `.env.example`.

## Security notes
- Never commit `.env`.
- Never make the S3 bucket public.
- Never expose AWS or Google secrets in browser code.
- Passwords use `password_hash()` / `password_verify()`.
- API endpoints check the logged-in user and file ownership.
- Share tokens are random and do not contain internal IDs.
- Downloads use short-lived S3 pre-signed URLs.

## Current project scope
Core cloud storage flows are real. AWS and Google features require your own credentials/configuration; the app intentionally fails clearly when those integrations are missing instead of showing fake cloud data.
