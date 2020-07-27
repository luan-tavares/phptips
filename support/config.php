<?php

/**
 * DATABASE
 */
define("CONFIG_DB_HOST", "localhost");
define("CONFIG_DB_PORT", "3306");
define("CONFIG_DB_USER", "root");
define("CONFIG_DB_PASSWORD", "");
define("CONFIG_DB_DATABASE", "phptips");

/**
 * URLS
 */
define("CONFIG_URL_BASE", "https://phptips");
define("CONFIG_URL_ERROR", CONFIG_URL_BASE ."/404");

/**
 * DATES
 */
define("CONFIG_DATE_BRAZIL", "d/m/Y H:i:s");
define("CONFIG_DATE_APP", "Y-m-d H:i:s");
define("CONFIG_DATE_TIMEZONE", "America/Sao_paulo");

/**
 * SESSIONS
 */
define("CONFIG_SESSION_PATH", __DIR__."/../storage/sessions");

/**
 * PASSWORD
 */
define("CONFIG_PASSWORD_MIN_LENGTH", 6);
define("CONFIG_PASSWORD_MAX_LENGTH", 15);
define("CONFIG_PASSWORD_ALGO", PASSWORD_DEFAULT);
define("CONFIG_PASSWORD_OPTION", ["cost"=>10]);


/**
 * MESSAGES
 */
define("CONFIG_MESSAGE_MAINCLASS", "message-box");

/**
 * EMAIL
 */
define("CONFIG_EMAIL_OPTION_SECURE", "tls");
define("CONFIG_EMAIL_OPTION_LANG", "br");
define("CONFIG_EMAIL_OPTION_CHARSET", "utf-8");
define("CONFIG_EMAIL_OPTION_AUTH", true);
define("CONFIG_EMAIL_OPTION_HTML", true);

define("CONFIG_EMAIL_HOST", "smtp.sendgrid.net");
define("CONFIG_EMAIL_USERNAME", "apikey");
define("CONFIG_EMAIL_PASSWORD", "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");
define("CONFIG_EMAIL_PORT", 587);
define("CONFIG_EMAIL_SENDER", ["address"=>"luan@inboundsoul.com", "name"=>"Inbound Soul"]);

/**
 * VIEWS
 */
define("CONFIG_VIEW_PATH", __DIR__."/../resources/views");
define("CONFIG_VIEW_EXT", "view.php");
