# Errores

Muchos errores se pueden lanzar mientras que usan la biblioteca, si usted no puede parecer encontrar lo que significa un error o qué usted está haciendo mal, echa un vistazo aquí.
Todo está estructurado de la siguiente manera:
#### [nombre del error]
[Cómo ocurre el error]
[fijar]
#### Función Imap no disponible
PHP no admite conexiones a servidores web vía imap
Para corregir esta descarga php_imap.dll y habilitarla, poniendo lo siguiente en php.ini `extension = php_gettext.dll`
#### El buzón no está instalado
No se proporcionó buzón
Confirme que la variable $ mailbox se llena al conectar
#### El buzón debe ser una cadena
El buzón proporcionado para conectarse al servidor web no es una cadena
No podemos conectarnos a las cajas de correo que tengan un número entero en ellas. Asegúrese de que la variable $ mailbox provied es una cadena
#### El nombre de usuario debe ser una cadena
El nombre de usuario proporcionado para conectarse al servidor web no es una cadena
Los servidores web no usan matrices como nombres de usuario !!! Asegúrese de que la variable $ username es una cadena
#### La contraseña debe ser una cadena
La contraseña proporcionada para conectarse al servidor web no es una cadena
Confirme que la variable $ password es una cadena
#### Las opciones deben ser un entero
La variable de opciones proporcionada al conectar no es un entero
// No lo sé. Alguien que manda la comprobación me dice y mal arreglo
#### N_ debe ser un entero
El número de reintentos proporcionados no es un número entero
Asegúrese de que la variable $ N_retries es un entero
#### Los parámetros deben ser una matriz
Los parámetros proporcionados para conectarse al servidor no son una matriz
// No lo sé. Alguien que manda la comprobación me dice y mal arreglo
#### Error al conectarse a [insertar la cadena del buzón aquí]
El cliente PHP-imap tuvo problemas para conectarse al buzón provisto con los detalles proporcionados
Esto puede significar muchas cosas. Esto puede significar que su buzón no es válido o que su nombre de usuario y contraseña no son válidos. Confirme sus datos de acceso y asegúrese de que su servidor de correo esté en línea
#### Option connect debe estar instalado
Si ha seleccionado la opción de conexión avanzada y no se ha instalado `connect` como
```php
$ Imap = new ImapClient ([
    'Connect' => [
        'Username' => 'usuario',
        'Password' => 'pasar',
    ]
]);
```
#### Se debe especificar el archivo para saveEmail ()
No especificaste una ruta de acceso de archivo
Asegúrese de que su código tenga este aspecto:
```php
$ Imap-> saveEmail ($ your_file_path_var, $ your_email_id_var, $ your_part_var)
```
#### ID de correo electrónico debe especificarse para saveEmail ()
No especificaste una ID de correo electrónico
Asegúrese de que su código tenga este aspecto:
```php
$ Imap-> saveEmail ($ your_file_path_var, $ your_email_id_var, $ your_part_var)
```
#### Archivo debe ser una cadena para saveEmail ()
La ruta del archivo proporcionada no es una cadena
Asegúrese de que su archivo $ es una cadena * no * un archivo abierto
#### $ id debe ser un entero para saveEmail ()
El ID de correo electrónico proporcionado es un número entero
Asegúrese de que su $ id es un número entero
