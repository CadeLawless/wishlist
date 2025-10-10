/**
 * Enhanced Popup Manager
 * Works alongside existing popup.js to provide additional functionality
 */
class PopupManager {
    constructor() {
        this.activePopups = new Set();
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupEventListeners());
        } else {
            this.setupEventListeners();
        }
    }
    
    setupEventListeners() {
        // Enhanced popup button handling
        $(document.body).on("click", ".popup-button:not(.disabled)", (e) => {
            this.handlePopupButtonClick(e);
        });
        
        // Enhanced close button handling
        $(document.body).on("click", ".close-container:not(.options-close)", (e) => {
            this.handleCloseButtonClick(e);
        });
        
        // Enhanced no button handling
        $(document.body).on("click", ".no-button", (e) => {
            this.handleNoButtonClick(e);
        });
        
        // Enhanced window click handling
        $(window).on("click", (e) => {
            this.handleWindowClick(e);
        });
        
        // Image popup handling
        $(document.body).on("click", ".image-popup-button", (e) => {
            this.handleImagePopupClick(e);
        });
        
        // Keyboard handling
        $(document).on("keydown", (e) => {
            this.handleKeydown(e);
        });
    }
    
    handlePopupButtonClick(e) {
        const button = e.currentTarget;
        e.preventDefault();
        
        // Add fixed class to body
        $("body").addClass("fixed");
        
        // Find and show the popup
        let popup;
        if (button.tagName === "INPUT") {
            popup = button.nextElementSibling.nextElementSibling.firstElementChild;
            button.nextElementSibling.nextElementSibling.classList.remove("hidden");
        } else {
            popup = button.nextElementSibling.firstElementChild;
            button.nextElementSibling.classList.remove("hidden");
        }
        
        popup.classList.add("active");
        
        // Track active popup
        const popupContainer = button.nextElementSibling.tagName === "DIV" ? 
            button.nextElementSibling : button.nextElementSibling.nextElementSibling;
        this.activePopups.add(popupContainer);
        
        // Trigger custom event
        $(popupContainer).trigger('popup:opened', [button]);
    }
    
    handleCloseButtonClick(e) {
        e.preventDefault();
        const closeButton = e.currentTarget;
        const popupContainer = closeButton.closest(".popup-container");
        
        // Don't close first/second popups unless they're not special types
        if (!popupContainer.classList.contains("first") && !popupContainer.classList.contains("second")) {
            $("body").removeClass("fixed");
        }
        
        this.hidePopup(popupContainer);
        
        // Clean up popup animations
        const popups = popupContainer.querySelectorAll(".popup:not(.first, .second)");
        popups.forEach(popup => {
            popup.classList.remove("slide-in-left", "slide-out-left", "slide-in-right", "slide-out-right", "hidden");
            if (popup.className.includes("yes")) {
                popup.classList.add("hidden");
            }
        });
        
        // Trigger custom event
        $(popupContainer).trigger('popup:closed');
    }
    
    handleNoButtonClick(e) {
        e.preventDefault();
        const noButton = e.currentTarget;
        const popupContainer = noButton.closest(".popup-container");
        
        // Don't close first/second popups unless they're not special types
        if (!popupContainer.classList.contains("first") && !popupContainer.classList.contains("second")) {
            $("body").removeClass("fixed");
        }
        
        this.hidePopup(popupContainer);
        
        // Handle double-no buttons
        if (noButton.classList.contains("double-no")) {
            const prevPopup = popupContainer.previousElementSibling;
            if (prevPopup && prevPopup.classList.contains("popup-container")) {
                this.hidePopup(prevPopup);
            }
        }
        
        // Trigger custom event
        $(popupContainer).trigger('popup:cancelled');
    }
    
    handleWindowClick(e) {
        const openPopups = $(".popup-container:not(.hidden)");
        const openDropdowns = $(".image-dropdown .options:not(.hidden)");
        
        if (openPopups.length > 0) {
            const isPopupButton = e.target.classList.contains("popup-button") || 
                                 e.target.classList.contains("image-popup-button");
            const isInsidePopup = e.target.closest(".popup-container") !== null && 
                                 !e.target.classList.contains("popup-container");
            
            if (!isPopupButton && !isInsidePopup) {
                if (openDropdowns.length > 0) {
                    // Close dropdowns first
                    openDropdowns.addClass("hidden");
                    openDropdowns.first().closest(".popup-content").removeClass("fixed static");
                } else {
                    // Close popups in order: second, first, then any others
                    const popupSecond = $(".popup-container.second:not(.hidden)");
                    const popupFirst = $(".popup-container.first:not(.hidden)");
                    
                    if (popupSecond.length > 0) {
                        this.hidePopup(popupSecond[0]);
                    } else if (popupFirst.length > 0) {
                        this.hidePopup(popupFirst[0]);
                    } else {
                        // Close all remaining popups
                        openPopups.each((index, popup) => {
                            this.hidePopup(popup);
                        });
                        document.body.classList.remove("fixed");
                    }
                }
            }
        }
    }
    
    handleImagePopupClick(e) {
        e.preventDefault();
        const button = e.currentTarget;
        const imageSrc = $(button).find(".item-image").attr("src");
        const popup = $(".image-popup-container").first();
        
        popup.removeClass("hidden");
        popup.find(".image-popup").addClass("active");
        popup.find(".popup-image").attr("src", imageSrc);
        
        // Track active popup
        this.activePopups.add(popup[0]);
        
        // Trigger custom event
        popup.trigger('popup:opened', [button]);
    }
    
    handleKeydown(e) {
        // Close popups with Escape key
        if (e.key === 'Escape' || e.keyCode === 27) {
            const openPopups = $(".popup-container:not(.hidden)");
            if (openPopups.length > 0) {
                // Close the topmost popup
                const topPopup = openPopups.last()[0];
                this.hidePopup(topPopup);
                
                // If no more popups, remove fixed class
                if ($(".popup-container:not(.hidden)").length === 0) {
                    document.body.classList.remove("fixed");
                }
            }
        }
    }
    
    hidePopup(popupElement) {
        if (typeof popupElement === 'string') {
            popupElement = document.getElementById(popupElement);
        }
        
        if (popupElement) {
            popupElement.classList.add("hidden");
            popupElement.querySelector(".popup")?.classList.remove("active");
            this.activePopups.delete(popupElement);
            
            // Trigger custom event
            $(popupElement).trigger('popup:closed');
        }
    }
    
    showPopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            popup.classList.remove("hidden");
            popup.querySelector(".popup")?.classList.add("active");
            document.body.classList.add("fixed");
            this.activePopups.add(popup);
            
            // Trigger custom event
            $(popup).trigger('popup:opened');
        }
    }
    
    // Utility methods
    getActivePopups() {
        return Array.from(this.activePopups);
    }
    
    closeAllPopups() {
        this.activePopups.forEach(popup => {
            this.hidePopup(popup);
        });
        document.body.classList.remove("fixed");
    }
    
    // Method to programmatically create and show a popup
    showConfirmation(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to proceed?',
            onConfirm = null,
            onCancel = null,
            confirmText = 'Yes',
            cancelText = 'No'
        } = options;
        
        // Create a temporary popup ID
        const popupId = 'temp-confirmation-' + Date.now();
        
        // This would need to be integrated with the PopupManager PHP class
        // For now, we'll use the existing popup structure
        console.log('Confirmation popup requested:', options);
        
        return popupId;
    }
}

// Initialize the popup manager
window.PopupManager = new PopupManager();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PopupManager;
}
