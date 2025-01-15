<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'poc' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         'I/IdzRBbQ0Z 3p4Aj jJGTG&,2l` /*)q@YL)TjF^{D_Vnsx+K[5@Bq:N;2iw5ev' );
define( 'SECURE_AUTH_KEY',  '*kTUCV,=v1o:vPy`ZW}fHS9jrfw13KxK-*9,:IV1E_;8daW2?XSiJ@N;Cs{N,zb[' );
define( 'LOGGED_IN_KEY',    'SVs9yBNGnF+g3B+-v:_~)6.l B5M$qa.T^[O}cvWTsO{LMG4./5p7HRxhfr;:w|`' );
define( 'NONCE_KEY',        'iw[}7=s+XAB{x6+B23HML*6P:]5[rN X^oTSaJoudm[ f=ym;qg33>f;Q-n_aJz1' );
define( 'AUTH_SALT',        '{jk0`a>NdMejQ[9V1rh!h3`Cz#;?R;lrElzSgdPN>Q?C7<9%_?IB+g^l> &EE/WQ' );
define( 'SECURE_AUTH_SALT', '$]Ag]TbMWX Hao/C Th)o4yBI0*svp5tCzt$ZBFFt7@xw822p**3!|Q`.N`#j~#|' );
define( 'LOGGED_IN_SALT',   'WbV_vUNx&9>]@ vfL*Iqa(0XN=%S4Q+Lp&N`tQJz[-/M=kQpFIJKQQjzG[eA2L-(' );
define( 'NONCE_SALT',       'V)Qt#i-+hme5A?/Py)~izmio`(Ytvv`Br 0;4;^%`]IY(6)~RsX$dx~>Q3h~bpl>' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'poc_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
