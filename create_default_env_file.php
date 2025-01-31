<?php 

function create_default_env_file()
{
    file_put_contents(".env", "LC_ALL=pt_BR.UTF-8
DB_PORT=3306
DB_HOST=127.0.0.1
DB_NAME=my_blog
DB_USER=root
DB_PASSWORD=");
}
