<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'thepredictionmachine_testing');

/** MySQL database username */
define('DB_USER', 'beppescavo');

/** MySQL database password */
define('DB_PASSWORD', 'h0r1z0nMRL');

/** MySQL hostname */
define('DB_HOST', 'mysql.timestreams.org.uk');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '6hs^*t%#wC$0E(sy1!sJZ~iLEXg7/C0u$rk5qZy78$G|A;Es2LFSt$UlT`SB|?U;');
define('SECURE_AUTH_KEY',  ')`4ZX"Y$ZI?5SITr_3*4B*!/`"pqGVja2|vNrT&?z7UA2"A6SKmaRZ!6goMQkh(F');
define('LOGGED_IN_KEY',    'IYSevX^Fy_qlHTu~Rb(gDp1sEM3A^?J~tz;RnXq%4fju%eSoO3G9D`l@s`8?*/#l');
define('NONCE_KEY',        '#p1bVe@b9zU@`uI@8Lp$gC9a(zBC#l#g+~UG8J_lfMSHeVW4:GAK(o+0$yt^itgm');
define('AUTH_SALT',        '7*bNNG@FTLK#X_ne/rweQ05P4WkSu~WgiaPxl4|J:bba:CHsxR1_eP&6U`BI"s;c');
define('SECURE_AUTH_SALT', 'q(QsZ0g$NbD)ORGF+z(H2Xn!hcPb#Vt?lq7i"nwRt+(ySF1w#CVFj/0?~uM(FMhv');
define('LOGGED_IN_SALT',   'JIFHrq1@o5o/0Sft#R:QFLD:CPm^p+B$:9#~H:j~uV#7zut0;04KqmyB~UrxuT(R');
define('NONCE_SALT',       'ywh~Wst34LOW$u3?EL@veXr`xS:BAGSez|eW|Z&UJ1iT@3T_pJApl)STTifs7cPK');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_ekx42t_';

/**
 * Limits total Post Revisions saved per Post/Page.
 * Change or comment this line out if you would like to increase or remove the limit.
 */
define('WP_POST_REVISIONS',  10);

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);
if (WP_DEBUG) {
    define('WP_DEBUG_DISPLAY', true);
    /* log php errors */
    @ini_set('display_errors', 0); /* enable or disable public display of errors (use 'On' or 'Off') */
    @ini_set('error_log', dirname(__FILE__) . '/wp-content/logs/php-errors.log'); /* path to server-writable log file */
    @ini_set( 'error_reporting', E_ALL ^ E_NOTICE ); /* the php parser to  all errors, excreportept notices.  */
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

