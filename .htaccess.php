<IfModule mod_rewrite.c>
    RewriteEngine On

    # اگر فایل/پوشه واقعی است، دست نزن
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # همه درخواست‌ها را به public بفرست
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
