/**
 * Gestionnaire de notifications
 */
class NotificationManager {
    constructor() {
        console.log('NotificationManager: Initialisation...');
        this.bell = document.getElementById('notification-bell');
        this.badge = document.getElementById('notification-badge');
        this.dropdown = document.getElementById('notification-dropdown');
        this.list = document.getElementById('notification-list');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        
        console.log('NotificationManager: Elements trouvés', {
            bell: !!this.bell,
            badge: !!this.badge,
            dropdown: !!this.dropdown,
            list: !!this.list
        });
        
        if (!this.bell || !this.badge || !this.dropdown || !this.list) {
            console.warn('Notification elements not found');
            return;
        }
        
        console.log('NotificationManager: Tous les éléments trouvés, initialisation...');
        this.init();
    }
    
    init() {
        console.log('NotificationManager: init() appelé');
        // Toggle dropdown au clic sur la cloche
        this.bell.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });
        
        // Fermer le dropdown si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!this.dropdown.contains(e.target) && e.target !== this.bell) {
                this.closeDropdown();
            }
        });
        
        // Marquer toutes comme lues
        this.markAllReadBtn?.addEventListener('click', () => {
            this.markAllAsRead();
        });
        
        // Charger les notifications au démarrage
        console.log('NotificationManager: Chargement des notifications...');
        this.loadNotifications();
        
        // Rafraîchir toutes les 30 secondes
        setInterval(() => this.loadNotifications(), 30000);
    }
    
    toggleDropdown() {
        this.dropdown.classList.toggle('show');
        
        // Si on ouvre le dropdown, marquer les notifications comme lues
        if (this.dropdown.classList.contains('show')) {
            this.markVisibleAsRead();
        }
    }
    
    closeDropdown() {
        this.dropdown.classList.remove('show');
    }
    
    async loadNotifications() {
        console.log('NotificationManager: loadNotifications() appelé');
        try {
            console.log('NotificationManager: Appel API /notification/list');
            const response = await fetch('/notification/list');
            console.log('NotificationManager: Réponse reçue', response.status);
            const data = await response.json();
            console.log('NotificationManager: Données reçues', data);
            
            this.updateBadge(data.unreadCount);
            this.renderNotifications(data.notifications);
        } catch (error) {
            console.error('Erreur lors du chargement des notifications:', error);
        }
    }
    
    updateBadge(count) {
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.classList.remove('d-none');
        } else {
            this.badge.classList.add('d-none');
        }
    }
    
    renderNotifications(notifications) {
        if (notifications.length === 0) {
            this.list.innerHTML = `
                <div class="notification-empty">
                    <i class="bi bi-bell"></i>
                    <p>Aucune notification</p>
                </div>
            `;
            return;
        }
        
        this.list.innerHTML = notifications.map(notif => this.renderNotification(notif)).join('');
        
        // Ajouter les event listeners
        this.list.querySelectorAll('.notification-item').forEach(item => {
            const notifId = item.dataset.notificationId;
            const notification = notifications.find(n => n.id == notifId);
            
            // Clic sur la notification
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.notification-actions')) {
                    this.handleNotificationClick(notification);
                }
            });
            
            // Boutons d'action pour les invitations
            const acceptBtn = item.querySelector('.accept-invitation');
            const rejectBtn = item.querySelector('.reject-invitation');
            const deleteBtn = item.querySelector('.delete-notification');
            
            acceptBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.acceptInvitation(notifId);
            });
            
            rejectBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.rejectInvitation(notifId);
            });
            
            deleteBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                this.deleteNotification(notifId);
            });
        });
    }
    
    renderNotification(notif) {
        const unreadClass = notif.isRead ? '' : 'unread';
        const typeIcon = notif.type === 'invitation' ? 'envelope-fill' : 'chat-dots-fill';
        const typeLabel = notif.type === 'invitation' ? 'Invitation' : 'Commentaire';
        
        let actions = '';
        if (notif.type === 'invitation') {
            actions = `
                <div class="notification-actions">
                    <button class="btn btn-sm btn-success accept-invitation">Accepter</button>
                    <button class="btn btn-sm btn-secondary reject-invitation">Refuser</button>
                </div>
            `;
        } else {
            actions = `
                <div class="notification-actions">
                    <button class="btn btn-sm btn-outline-danger delete-notification">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }
        
        return `
            <div class="notification-item ${unreadClass}" data-notification-id="${notif.id}" data-link="${notif.link}">
                <div class="notification-header">
                    <div class="notification-type">
                        <i class="bi bi-${typeIcon}"></i>
                        <span>${typeLabel}</span>
                    </div>
                    <div class="notification-date">${notif.createdAt}</div>
                </div>
                <div class="notification-message">${notif.message}</div>
                ${actions}
            </div>
        `;
    }
    
    handleNotificationClick(notification) {
        // Marquer comme lue
        this.markAsRead(notification.id);
        
        // Rediriger vers le lien
        if (notification.link) {
            window.location.href = notification.link;
        }
    }
    
    async markAsRead(notificationId) {
        try {
            await fetch(`/notification/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            this.loadNotifications();
        } catch (error) {
            console.error('Erreur lors du marquage comme lue:', error);
        }
    }
    
    markVisibleAsRead() {
        // Marquer automatiquement les notifications non lues comme lues quand on ouvre le dropdown
        const unreadItems = this.list.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            const notifId = item.dataset.notificationId;
            if (notifId) {
                this.markAsRead(notifId);
            }
        });
    }
    
    async markAllAsRead() {
        try {
            await fetch('/notification/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            this.loadNotifications();
        } catch (error) {
            console.error('Erreur lors du marquage de toutes comme lues:', error);
        }
    }
    
    async deleteNotification(notificationId) {
        try {
            await fetch(`/notification/${notificationId}/delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            this.loadNotifications();
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
        }
    }
    
    async acceptInvitation(notificationId) {
        try {
            const response = await fetch(`/notification/${notificationId}/accept-invitation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success && data.redirectUrl) {
                window.location.href = data.redirectUrl;
            } else {
                console.error('Erreur lors de l\'acceptation:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors de l\'acceptation de l\'invitation:', error);
        }
    }
    
    async rejectInvitation(notificationId) {
        if (!confirm('Êtes-vous sûr de vouloir refuser cette invitation ?')) {
            return;
        }
        
        try {
            const response = await fetch(`/notification/${notificationId}/reject-invitation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                this.loadNotifications();
            } else {
                console.error('Erreur lors du refus:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors du refus de l\'invitation:', error);
        }
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    new NotificationManager();
});
