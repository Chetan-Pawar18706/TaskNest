# TaskNest Production Checklist

## Security

- [ ] Change `JWT_SECRET` in `config/db.php` to a unique 32+ character string
- [ ] Set `DEBUG` to `false` in `config/db.php`
- [ ] Set `LOG_ERRORS` to `true` in `config/db.php`
- [ ] Set `SESSION_TIMEOUT` to appropriate value (default: 1800s = 30min)
- [ ] Ensure `uploads/` directory is not directly executable
- [ ] Add `.htaccess` to `uploads/` to prevent PHP execution
- [ ] Verify all file upload directories have correct permissions (755)
- [ ] Review PHPMailer configuration for production SMTP
- [ ] Enable HTTPS and set Secure flag on cookies
- [ ] Set proper CORS headers if needed
- [ ] Remove any debug output or `var_dump` calls

## Database

- [ ] Run `database/install.sql` to create all tables
- [ ] Verify all foreign key constraints are working
- [ ] Add database backups schedule
- [ ] Optimize MySQL settings for production
- [ ] Remove test data if any

## Server Configuration

- [ ] Ensure PHP 8.0+ is installed
- [ ] Enable required PHP extensions: `mysqli`, `mbstring`, `json`, `fileinfo`
- [ ] Set `upload_max_filesize` to at least 10M
- [ ] Set `post_max_size` to at least 12M
- [ ] Set `max_execution_time` to 30 or higher
- [ ] Configure error logging to file, not display

## File System

- [ ] `uploads/avatars/` - writable (755)
- [ ] `uploads/notes/` - writable (755)
- [ ] `uploads/documents/` - writable (755)
- [ ] `logs/` - writable (755)
- [ ] `.gitignore` includes `uploads/` and `logs/`

## Functionality Testing

- [ ] User registration works
- [ ] User login/logout works
- [ ] Password reset flow works
- [ ] Dashboard loads with correct data
- [ ] Tasks CRUD operations work
- [ ] Notes CRUD + image upload work
- [ ] Expenses CRUD + charts work
- [ ] Documents upload/download/preview work
- [ ] Borrow/Lend tracking works
- [ ] Habits logging + charts work
- [ ] Goals progress tracking works
- [ ] Shopping list operations work
- [ ] Admin panel accessible only to admins
- [ ] CSV export works
- [ ] Search and filters work across modules
- [ ] Pagination works correctly
- [ ] Responsive design on mobile/tablet
- [ ] Dark mode toggle works
- [ ] Toast notifications display correctly

## Performance

- [ ] CSS files are minified (optional)
- [ ] JavaScript files are minified (optional)
- [ ] Database queries are optimized (indexes in place)
- [ ] Images are properly sized
- [ ] No unnecessary database queries on page load

## Backup Strategy

- [ ] Database backup scheduled (daily recommended)
- [ ] File system backup includes uploads
- [ ] Backup restoration tested
- [ ] Backup storage is secure and separate

## Monitoring

- [ ] Error logging is active (`logs/errors.log`)
- [ ] Application logging is active (`logs/app.log`)
- [ ] Server resource monitoring in place
- [ ] Database connection pooling if needed

## Deployment

- [ ] All files uploaded to production server
- [ ] Database imported on production
- [ ] Config files updated for production environment
- [ ] DNS configured correctly
- [ ] SSL certificate installed (if using HTTPS)
- [ ] Cron jobs set up for any scheduled tasks
