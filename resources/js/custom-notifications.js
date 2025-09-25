/**
 * Custom Notification System
 * A reusable notification system that matches Filament's design language
 *
 * Usage:
 * - showNotification('success', 'Message here')
 * - showNotification('error', 'Error message')
 * - showNotification('warning', 'Warning message')
 * - showNotification('info', 'Info message')
 */

class CustomNotificationSystem {
    constructor() {
        this.notifications = [];
        this.container = null;
        this.init();
    }

    init() {
        // Create notification container if it doesn't exist
        this.createContainer();
    }

    createContainer() {
        this.container = document.getElementById(
            "custom-notifications-container"
        );
        if (!this.container) {
            this.container = document.createElement("div");
            this.container.id = "custom-notifications-container";
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 99999;
                pointer-events: none;
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-width: 400px;
                width: 100%;
                padding: 0 20px;
                box-sizing: border-box;
            `;
            document.body.appendChild(this.container);
        }
    }

    show(type, message, options = {}) {
        const notification = this.createNotification(type, message, options);
        this.notifications.push(notification);
        this.container.appendChild(notification.element);

        // Animate in
        this.animateIn(notification.element);

        // Auto remove after duration
        const duration = options.duration || this.getDefaultDuration(type);
        setTimeout(() => {
            this.remove(notification.id);
        }, duration);

        return notification.id;
    }

    createNotification(type, message, options = {}) {
        const id = this.generateId();
        const notification = document.createElement("div");

        // Base styles
        notification.style.cssText = `
            background: ${this.getBackgroundColor(type)};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: ${this.getShadow(type)};
            font-size: 14px;
            font-weight: 500;
            word-wrap: break-word;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transform: translateX(100%);
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: auto;
            cursor: pointer;
            max-width: 100%;
            box-sizing: border-box;
        `;

        // Add icon and message
        notification.innerHTML = `
            ${this.getIcon(type)}
            <span style="flex: 1;">${message}</span>
            ${options.closable !== false ? this.getCloseButton() : ""}
        `;

        // Add click to close functionality
        if (options.closable !== false) {
            notification.addEventListener("click", () => {
                this.remove(id);
            });
        }

        return {
            id,
            element: notification,
            type,
            message,
            createdAt: Date.now(),
        };
    }

    animateIn(element) {
        requestAnimationFrame(() => {
            element.style.transform = "translateX(0)";
        });
    }

    animateOut(element, callback) {
        element.style.transform = "translateX(100%)";
        element.style.opacity = "0";
        setTimeout(() => {
            if (callback) callback();
        }, 300);
    }

    remove(id) {
        const notificationIndex = this.notifications.findIndex(
            (n) => n.id === id
        );
        if (notificationIndex === -1) return;

        const notification = this.notifications[notificationIndex];
        this.animateOut(notification.element, () => {
            if (notification.element.parentNode) {
                notification.element.parentNode.removeChild(
                    notification.element
                );
            }
            this.notifications.splice(notificationIndex, 1);
        });
    }

    clearAll() {
        this.notifications.forEach((notification) => {
            this.animateOut(notification.element, () => {
                if (notification.element.parentNode) {
                    notification.element.parentNode.removeChild(
                        notification.element
                    );
                }
            });
        });
        this.notifications = [];
    }

    getBackgroundColor(type) {
        const colors = {
            success: "linear-gradient(135deg, #10b981, #059669)",
            error: "linear-gradient(135deg, #ef4444, #dc2626)",
            warning: "linear-gradient(135deg, #f59e0b, #d97706)",
            info: "linear-gradient(135deg, #3b82f6, #2563eb)",
        };
        return colors[type] || colors.info;
    }

    getShadow(type) {
        const shadows = {
            success:
                "0 10px 25px rgba(16, 185, 129, 0.3), 0 4px 6px rgba(0, 0, 0, 0.1)",
            error: "0 10px 25px rgba(239, 68, 68, 0.3), 0 4px 6px rgba(0, 0, 0, 0.1)",
            warning:
                "0 10px 25px rgba(245, 158, 11, 0.3), 0 4px 6px rgba(0, 0, 0, 0.1)",
            info: "0 10px 25px rgba(59, 130, 246, 0.3), 0 4px 6px rgba(0, 0, 0, 0.1)",
        };
        return shadows[type] || shadows.info;
    }

    getIcon(type) {
        const icons = {
            success: `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>`,
            error: `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>`,
            warning: `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>`,
            info: `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>`,
        };
        return icons[type] || icons.info;
    }

    getCloseButton() {
        return `<button style="
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
            opacity: 0.7;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>`;
    }

    getDefaultDuration(type) {
        const durations = {
            success: 4000,
            error: 6000,
            warning: 5000,
            info: 4000,
        };
        return durations[type] || 4000;
    }

    generateId() {
        return (
            "notification_" +
            Date.now() +
            "_" +
            Math.random().toString(36).substr(2, 9)
        );
    }
}

// Initialize the notification system
const notificationSystem = new CustomNotificationSystem();

// Export functions for global use
window.showNotification = function (type, message, options = {}) {
    return notificationSystem.show(type, message, options);
};

window.hideNotification = function (id) {
    notificationSystem.remove(id);
};

window.clearAllNotifications = function () {
    notificationSystem.clearAll();
};

// Convenience functions for common notification types
window.showSuccessNotification = function (message, options = {}) {
    return notificationSystem.show("success", message, options);
};

window.showErrorNotification = function (message, options = {}) {
    return notificationSystem.show("error", message, options);
};

window.showWarningNotification = function (message, options = {}) {
    return notificationSystem.show("warning", message, options);
};

window.showInfoNotification = function (message, options = {}) {
    return notificationSystem.show("info", message, options);
};

// Export the class for advanced usage
window.CustomNotificationSystem = CustomNotificationSystem;
