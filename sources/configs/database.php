<?php
define("DB_HOST", getenv("DB_HOST") ?: "db");
define("DB_USER", getenv("DB_USERNAME") ?: "root");
define("DB_PASSWORD", getenv("DB_PASSWORD") ?: "123456");
define("DB_NAME", getenv("DB_DATABASE") ?: "app_web1");
define("DB_PORT", getenv("DB_PORT") ?: 3306);
