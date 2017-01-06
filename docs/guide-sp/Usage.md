# Uso
#### Después de instalar prep
Después de instalar esta biblioteca, sin embargo lo hizo, asegúrese de que el archivo que desea hacer la conexión con incluye las clases necesarias
Una conexión básica puede tener este aspecto:
```php
$buzón = 'my.imapserver.com';
$username = 'myuser';
$password = 'secreto';
$encryption = Imap :: ENCRYPT_SSL; // o ImapClient :: ENCRYPT_SSL o ImapClient :: ENCRYPT_TLS o null
$imap = nuevo Imap ($buzón, $username, $password, $encryption);

if($imap->isConnected() === false) {
    die($ imap->getError());
}
```
El código anterior lo conecta a un servidor de correo y se asegura de que esté conectado. Cambie las variables a su información
#### Después de la conexión
Hay muchas cosas que puedes hacer después del código anterior.
Por ejemplo, puede obtener y hacer eco de todas las carpetas
```php
$folders = $ imap->getFolders(); // devuelve matriz de cadenas
foreach ($folders as $folders) {
    Carpeta echo $;
}
```
Ahora puede seleccionar una carpeta:

```php
$Imap->selectFolder("Inbox");
```
Una vez que ha seleccionado una carpeta puede contar los mensajes en esta carpeta:

```php
$overallMessages = $ imap->countMessages();
$unreadMessages = $ imap->countUnreadMessages();
```
Bueno, ahora vamos a buscar todos los correos electrónicos en la carpeta seleccionada actualmente (en nuestro ejemplo la "Bandeja de entrada"):

```php
$emails = $ imap->getMessages();
var_dump($emails);
```
ADVERTENCIA !!!!: getMessages() no marcará los correos electrónicos como leídos! Devolverá la siguiente estructura sin cambiar los correos electrónicos. En este ejemplo, hay dos correos electrónicos en la Bandeja de entrada.

```
array(2) {
  [0]=>
  array(8) {
    ["to"]=>
    array(1) {
      [0]=>
      string(30) "Tobias Zeising <tobias.zeising@aditu.de>"
    }
    ["from"]=>
    string(30) "Karl Mustermann <karl.mustermann@aditu.de>"
    ["date"]=>
    string(31) "Fri, 27 Dec 2013 18:44:52 +0100"
    ["subject"]=>
    string(12) "Test Subject"
    ["id"]=>
    int(15)
    ["unread"]=>
    bool(true)
    ["answered"]=>
    bool(false)
    ["body"]=>
    string(240) "<p>This is a test body.</p>

    <p>With a bit <em><u>html</u></em>.</p>

    <p>and without <span style="color:#008000"><span style="font-size:14px"><span style="font-family:arial,helvetica,sans-serif">attachment</span></span></span></p>
    "
  }
  [1]=>
  array(9) {
    ["to"]=>
    array(1) {
      [0]=>
      string(29) "tobias.zeising@aditu.de <tobias.zeising@aditu.de>"
    }
    ["from"]=>
    string(40) "Karl Ruediger <karl.ruediger@aditu.de>"
    ["date"]=>
    string(31) "Thu, 19 Dec 2013 17:45:37 +0100"
    ["subject"]=>
    string(19) "Test mit Attachment"
    ["id"]=>
    int(14)
    ["unread"]=>
    bool(false)
    ["answered"]=>
    bool(false)
    ["body"]=>
    string(18) "Anbei eine Datei"
    ["attachments"]=>
    array(1) {
      [0]=>
      array(2) {
        ["name"]=>
        string(24) "640 x 960 (iPhone 4).jpg"
        ["size"]=>
        int(571284)
      }
    }
  }
}
```
También puede agregar / renombrar / eliminar carpetas. Permite agregar una nueva carpeta:

```php
$imap->addFolder('archivo');
```
Ahora vamos a mover el primer correo electrónico a esta carpeta

```php
$imap->moveMessage($emails[0]['id'], 'archivo');
```
Y eliminamos el segundo correo electrónico de la bandeja de entrada

```php
$ Imap->deleteMessage($emails[1]['id']);
```

También podemos guardar correos electrónicos
```php
// Nota: para servidores web más lentos, menos RAM utilizará saveEmailSafe()
$imap->saveEmail('archivo / users / johndoe / email_1.eml', 1);
```

Para obtener una lista completa de los métodos que puede hacer comprobar [lista actual de métodos] (Methods.md).

#### Conexión avanzada

También puede utilizar el código a continuación para agregar más opciones mientras se conecta

```php
$imap = new ImapClient ([
    'Flags' => [
        'Servicio' => ImapConnect :: SERVICE_IMAP, # ImapConnect :: SERVICE_IMAP, ImapConnect :: SERVICE_POP3, ImapConnect :: SERVICE_NNTP
        'Cifrar' => ImapConnect :: ENCRYPT_SSL, # ImapConnect :: ENCRYPT_SSL, ImapConnect :: ENCRYPT_TLS, ImapConnect :: ENCRYPT_NOTLS
        'ValidateCertificates' => ImapConnect :: NOVALIDATE_CERT, # ImapConnect :: VALIDATE_CERT, ImapConnect :: NOVALIDATE_CERT
        # ... y otra
    ],
    'Buzón' => [
        'Remote_system_name' => 'imap.server.ru',
        'Puerto' => '431',
        'Mailbox_name' => 'INBOX.Send',
        # ... y otra
    ],
    'Connect' => [
        'Username' => 'usuario',
        'Password' => 'pasar',
        # ... y otra
    ]
]);
```
 Todas las opciones de conexión que puede ver en el archivo example-connect.php
 O vaya [Conexión avanzada] (AdvancedConnecting.md)
 O puede ver el código ImapConnect clase.
