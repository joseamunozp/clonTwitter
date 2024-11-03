document.addEventListener("DOMContentLoaded", function() {
    const followButtons = document.querySelectorAll("button[name='follow_action']");
    
    followButtons.forEach(button => {
        button.addEventListener("click", function(event) {
            const action = button.value === 'follow' ? 'seguir' : 'dejar de seguir';
            const confirmAction = confirm(`¿Estás seguro de que deseas ${action} a este usuario?`);
            if (!confirmAction) {
                event.preventDefault();
            }
        });
    });
    
    // Confirmación para cierre de sesión
    const logoutButton = document.querySelector("#logoutButton");
    if (logoutButton) {
        logoutButton.addEventListener("click", function(event) {
            const confirmLogout = confirm("¿Estás seguro de que deseas cerrar sesión?");
            if (!confirmLogout) {
                event.preventDefault();
            }
        });
    }
});