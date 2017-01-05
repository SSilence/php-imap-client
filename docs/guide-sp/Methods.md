# Métodos

Los métodos siguientes están actualmente disponibles.

* `` __construct ($ mailbox, $ username, $ password, $ encryption) `` abre una nueva conexión de imap
* `` isConnected () `` compruebe si la conexión del imap se pudo abrir con éxito
* `` getError () `` devuelve el último mensaje de error
* `` selectFolder ($ folder) `` seleccione la carpeta actual
* `` getFolders () `` obtiene todas las carpetas disponibles
* `` setEmbed ($ val) `` Si es true, inserte todas las imágenes 'inline' en el cuerpo HTML, accesible en 'body_embed'
* `` countMessages () `` cuenta los mensajes en la carpeta actual
* `` countUnreadMessages () `` cuenta mensajes no leídos en la carpeta actual
* `` getMessages ($ withbody = true, $ standard = "UNSEEN") `` obtener correos electrónicos en la carpeta actual
* `` getMessage ($ id, $ withbody = true) `` recibe el correo electrónico por el id dado
* `` getUnreadMessages ($ withbody = true) `` obtiene mensajes no leídos en la carpeta actual
* `` deleteMessage ($ id) `` borrar mensaje con id dado
* `` deleteMessages ($ ids) `` borrar mensajes con ids dados (como array)
* `` moveMessage ($ id, $ target) `` mueve el mensaje con la id dada en la nueva carpeta
* `` moveMessages ($ ids, $ target) `` mueve mensajes con ids dados (como matriz) en una nueva carpeta
* `` setUnseenMessage ($ id, $ seen = true) `` establece el estado invisible del mensaje con el id dado
* `` getAttachment ($ id, $ index = 0) `` obtiene el archivo adjunto del mensaje con id dado (getMessages devuelve todos los archivos adjuntos disponibles)
* `` getQuota ($ user) `` Recupera la configuración del nivel de cuota y la estática de uso por buzón.
* `` getQuotaRoot ($ user) `` Recupera la configuración de nivel de cuota y la estática de uso por buzón.
* `` addFolder ($ name) `` agregar una nueva carpeta con nombre dado
* `` removeFolder ($ name) `` eliminar carpeta con nombre fiven
* `` renameFolder ($ name, $ newname) `` renombrar carpeta con nombre dado
* `` purge () `` mover todos los correos electrónicos de la carpeta actual a la papelera. Los correos electrónicos en la basura y el spam se eliminarán
* `` setEncoding () `` Identifica el atributo charset en el encabezado
* `` convertToUtf8 () `` Aplicar la codificación definida en el encabezado
* `` getAllEmailAddresses () `` devuelve todas las direcciones de correo electrónico de todos los correos electrónicos (para la lista de sugerencias automáticas)
* `` saveMessageInSent ($ header, $ body) `` guardar un mensaje enviado en la carpeta enviada
* `` getMailboxStatistics () `` devuelve estadísticas, vea [imap_mailboxmsginfo] (http://php.net/manual/de/function.imap-mailboxmsginfo.php)
* `` saveEmail ($ file, $ id, $ part) `` guarda un correo electrónico en el archivo $ file
* `` saveEmailSafe ($ file, $ id, $ part, $ streamFilter) `` guarda un correo electrónico en el archivo $ file. Esto se recomienda para servidores con bajas cantidades de RAM. Filtro de flujo se establece en convert.base64-decodificar de forma predeterminada
