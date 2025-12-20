// Badge System Interactivity
class BadgeSystem {
    constructor() {
        this.init();
    }

    init() {
        this.setupRedeemButtons();
        this.setupAnimations();
        this.setupProgressTracking();
    }

    setupRedeemButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-redeem')) {
                this.redeemBadge(e.target);
            }
        });
    }

    redeemBadge(button) {
        const badgeName = button.getAttribute('data-badge-name');
        
        // Show confirmation with fun animation
        if (confirm(`🎉 Wah! Anda nak tebus lencana "${badgeName}"?`)) {
            // Add loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menebus...';
            button.disabled = true;

            // Simulate API call
            setTimeout(() => {
                // Success animation
                button.classList.remove('btn-redeem');
                button.classList.add('btn-redeemed');
                button.innerHTML = '<i class="fas fa-check me-2"></i>Telah Ditebus! 🎉';
                
                // Show celebration
                this.showCelebration(badgeName);
                
                // Update badge card
                const badgeCard = button.closest('.badge-card');
                badgeCard.classList.remove('redeemable');
                badgeCard.classList.add('redeemed');
                
            }, 1500);
        }
    }

    showCelebration(badgeName) {
        // Create celebration element
        const celebration = document.createElement('div');
        celebration.className = 'celebration-toast';
        celebration.innerHTML = `
            <div class="celebration-content">
                <i class="fas fa-trophy celebration-icon"></i>
                <div>
                    <h4>Tahniah! 🎊</h4>
                    <p>Anda dapat lencana "${badgeName}"!</p>
                </div>
            </div>
        `;

        // Add styles
        celebration.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #5FAD56, #2454FF);
            color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 1000;
            animation: slideInRight 0.5s ease;
        `;

        document.body.appendChild(celebration);

        // Remove after 5 seconds
        setTimeout(() => {
            celebration.style.animation = 'slideOutRight 0.5s ease';
            setTimeout(() => celebration.remove(), 500);
        }, 5000);
    }

    setupAnimations() {
        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .celebration-content {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .celebration-icon {
                font-size: 2rem;
            }
        `;
        document.head.appendChild(style);
    }

    setupProgressTracking() {
        // Simulate progress updates
        const progressBadges = document.querySelectorAll('.badge-card.in-progress');
        progressBadges.forEach(badge => {
            const progressBar = badge.querySelector('.progress-bar');
            const progressText = badge.querySelector('.progress-text');
            
            if (progressBar && progressText) {
                // Animate progress bar
                setTimeout(() => {
                    const currentWidth = parseInt(progressBar.style.width);
                    const newWidth = Math.min(currentWidth + 20, 100);
                    progressBar.style.width = newWidth + '%';
                    progressText.textContent = newWidth + '%';
                    
                    if (newWidth === 100) {
                        const button = badge.querySelector('.badge-button');
                        button.classList.remove('btn-progress');
                        button.classList.add('btn-redeem');
                        button.innerHTML = 'Tebus Sekarang!';
                        button.disabled = false;
                        badge.classList.remove('in-progress');
                        badge.classList.add('redeemable');
                    }
                }, 2000);
            }
        });
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    new BadgeSystem();
});

// Add some fun interactive elements
document.addEventListener('DOMContentLoaded', () => {
    // Add hover effects to all badge cards
    const badgeCards = document.querySelectorAll('.badge-card');
    
    badgeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Add click sound effect (optional)
    const buttons = document.querySelectorAll('.badge-button');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // You can add a sound effect here if needed
            console.log('Button clicked:', this.textContent);
        });
    });
});