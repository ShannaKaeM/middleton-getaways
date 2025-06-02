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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'v3^Y{/U^w=KCLjUZQu*wb8TCF5GaU#@dd<4!$2L=Ore4f0.3zC4#$zaM5a&dL&cq' );
define( 'SECURE_AUTH_KEY',   'tk]c%YB]DKsb+=B$0<go$czw^&>{-_/S,#v_42<+>_Gg|P?+^I]uXCLCp(WdP}Qh' );
define( 'LOGGED_IN_KEY',     'g2oIgf&PjZp-|SU$Q=lEkNOBuwU.O^f=egx!c:=K;q^4rOh,k3jr&2}15acf-:$m' );
define( 'NONCE_KEY',         'G[$we0Qgw`;w#c7V2eZB|A5*%=FbL9wfU0bXT9zkau}N7O{zR=ZDW!JJs):z&ktU' );
define( 'AUTH_SALT',         'Re>^Re<zYsy(<IrGjDNKfV9!xI9`y*~C)LXw:&{+ ?P_Gb]9s1VU):s-}`>18_js' );
define( 'SECURE_AUTH_SALT',  '^6M:+7|stemX&[?[3mIo&z=]jA%VvIdF`n|T#3wm{um}k/-itf~5Z%L^v^,yFm(|' );
define( 'LOGGED_IN_SALT',    ' m{f}q*Yg?*MPR*SbM0 X0 <.o_^#{+P)04$uZ%v~=!>!%<ln@2%m;[eR#1#Pn{A' );
define( 'NONCE_SALT',        '+Hzl:D;}cI~;i&O@cYeQ&5tkz=gE`oMR6:zQOFr<`tMiL4r{UnH@RO 6v|J$W O;' );
define( 'WP_CACHE_KEY_SALT', 'N5q<8L;~aAvbHYa9V(+EA 3WMwxx=d$BKK x(`Ri)@(5hL9N]6y=-U8EQ#=Io 3S' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

// Load Composer autoloader
if ( file_exists( dirname( __DIR__, 2 ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
}



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
