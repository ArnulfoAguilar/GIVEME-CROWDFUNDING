<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'CET');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', '');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '1O*RrjAcx$i.!g)+0smZ*y/ffRfYF]eMv%fUphsig=n MOb`R78As?2[8K_;ez,k');
define('SECURE_AUTH_KEY', '`U>5wY]w_A:t|V<u!53X5>;&je}+aa]eYMJqEyH4CX5Tn[f/k6p^ IhEPWOG {~7');
define('LOGGED_IN_KEY', 'ud~C0Hq<cIY!6 P^(XpL)}{W-0GMTA]VIC>NP*&i4 w,.Gk~ u<1p|sh_ypF;JQ{');
define('NONCE_KEY', 'MB<Q=qswb#k+.pRlqyc b<6_&zX-p|,nf1Gv#yeJTS9,eyTQ8?=4j/2z8Jy-/*sF');
define('AUTH_SALT', 'Fie|j7D),vmYQ5*eX2c* ,= c#C=_7;ga#v8pO&^uY7i5yf.+y}[?g=alN}O3tvJ');
define('SECURE_AUTH_SALT', '+031$a,Dy*M_*!h>}z&I@T_?^+nZ9L1V@hqPr,Lq<z!NVe:C7%-.I9V2HrEz%Ii:');
define('LOGGED_IN_SALT', 'C$_xI??Agde.*hh.AD^9/Ztat4d8dpAaaH8i@MbUVaeAezLpzm?@tF*`jEJ95zq7');
define('NONCE_SALT', '6tR}D5.G5V)<jsvZ*2]5Rf>ovd@X0$DE^oWQ2bUJR!]V+MW9>tDs*-};:eT76fmo');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

