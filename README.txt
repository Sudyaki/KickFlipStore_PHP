Kick Flip Collection - PHP + MySQL version
================================
1. Install XAMPP, start Apache and MySQL.
2. Copy this folder to C:\xampp\htdocs\ProjectWEB_PHP
3. Open http://localhost/phpmyadmin -> Import -> choose database.sql
4. If your MySQL has a password, edit DB_USER / DB_PASS in config.php
5. Open http://localhost/ProjectWEB_PHP/setup.php, create your admin account,
   then DELETE setup.php.
6. Open http://localhost/ProjectWEB_PHP/ and log in.

Admin: Products (Create/Read/Update/Delete + image upload), Orders (view).
Customers: register, browse/search, add to cart, remove, checkout.

Logo: images/logo.png. Home banner: images/banner.jpg (swap in a higher-resolution banner if you have one).

Products: 14 shoe products come pre-loaded with their photos (uploads/ folder).
Prices are placeholders. Edit name/price/description from Admin > Products.
If you already imported the old database.sql, drop the 'projectweb' database in phpMyAdmin and import again.

Admin registration: choose 'Register as Admin' on register.php and enter the confirmation code.
The code is ADMIN_CODE in config.php - CHANGE IT to your own secret before using the site.

Dark mode: use the Dark/Light button (top right). It follows your device setting the first time and remembers your choice.
