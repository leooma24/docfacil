<?php

/*
 * Mensajes de autenticacion.
 *
 * Faltaba este archivo: con APP_LOCALE=es y APP_FALLBACK_LOCALE=es, Laravel
 * no tenia de donde sacarlos ni a que idioma caer, asi que imprimia la clave
 * cruda. Quien tecleaba mal su contraseña veia "auth.failed" en pantalla.
 */

return [
    'failed' => 'Ese correo y esa contraseña no coinciden. Revísalos e intenta otra vez.',
    'password' => 'La contraseña no es correcta.',
    'throttle' => 'Demasiados intentos. Espera :seconds segundos y vuelve a intentar.',
];
