<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('REVISR_GIT_PATH', 'https://github.com/Amarveer-banwait/classroom.git'); // Added by Revisr
define( 'DB_NAME', 'class' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ' iwV_+5OIF}45sZ9h1*4FNS]9oJ^^,jnO-^Y?N0 T*cFU;JPRdaVB=`@.yFyZ=d5' );
define( 'SECURE_AUTH_KEY',  '+xCa|}rTD-t;E1ldbX|}E}<^fa&M&|MFZ0.E}F^~mv&H</po3x|QjMy~*S/xWkt=' );
define( 'LOGGED_IN_KEY',    'K`:{(=BUatAB<q96fD@D{6[eiO17/IK=YF]|fnBnRA?%G#HEFcue>zV4QU@jBy-7' );
define( 'NONCE_KEY',        ')ve)D4d*i!@}Lrge._jI&zbZ+&Ad3g5VG$a##(NC8p4T?pq5-_.Fq$/cjIVU>;J:' );
define( 'AUTH_SALT',        'Pm_&#EFNxK.i+_w$W3Q{fi95UGP<d;(gABC_1 stfd$P6-H{]$k}7x6a)PGW>2`8' );
define( 'SECURE_AUTH_SALT', '/QsRvZb}GB$uh3BRdOw11UoEa(<vQ3NJXOc<Nf5kO:;JN)sE@15P?+2OIZSo9>/a' );
define( 'LOGGED_IN_SALT',   'izG5^V~>5m7/Jh<06z:|Zcmwdf@xCsdm(6</0nc3;iZjPVe*tKra`Cj&$a~%bK~H' );
define( 'NONCE_SALT',       'wm|6L?qPURAg36tH1x`0_(`Q*K&I9[vT4sP,UOd2k(2:I10Gv?}y^m!=WvuVT*[^' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
