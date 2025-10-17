document.addEventListener('DOMContentLoaded', function() {

    // Obtener el objeto URLSearchParams para leer los parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('msg');
    const notificationDiv = document.getElementById('notification-message');

    if (status && message && notificationDiv) {
    // Decodificar el mensaje para mostrar espacios y caracteres especiales
    const decodedMessage = decodeURIComponent(message);

    // Mostrar el div de notificación
    notificationDiv.style.display = 'block';
    notificationDiv.textContent = decodedMessage;

    if (status === 'success') {
    // Estilo para éxito (verde)
    notificationDiv.style.backgroundColor = '#d4edda';
    notificationDiv.style.color = '#155724';
} else if (status === 'error') {
    // Estilo para error (rojo)
    notificationDiv.style.backgroundColor = '#f8d7da';
    notificationDiv.style.color = '#721c24';
}

    // Limpiar la URL después de mostrar el mensaje (para que no aparezca al actualizar)
    // Se requiere History API, que es seguro en navegadores modernos.
    if (window.history.replaceState) {
    const cleanUrl = window.location.pathname;
    window.history.replaceState(null, '', cleanUrl);
}
}
});
