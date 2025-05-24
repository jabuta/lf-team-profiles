// Minimal JavaScript for browsers that don't support popover API
(function() {
    // Check if popover API is supported
    if (!HTMLElement.prototype.hasOwnProperty('popover')) {
        console.log('Popover API not supported, implementing fallback');
        
        document.addEventListener('DOMContentLoaded', function() {
            // Simple fallback implementation
            var buttons = document.querySelectorAll('[popovertarget]');
            
            buttons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = button.getAttribute('popovertarget');
                    var popover = document.getElementById(targetId);
                    
                    if (popover) {
                        // Toggle visibility
                        if (popover.style.display === 'block') {
                            popover.style.display = 'none';
                        } else {
                            // Hide all other popovers
                            document.querySelectorAll('.lf-team-popover').forEach(function(p) {
                                p.style.display = 'none';
                            });
                            popover.style.display = 'block';
                        }
                    }
                });
            });
            
            // Close on backdrop click
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('lf-team-popover')) {
                    e.target.style.display = 'none';
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.lf-team-popover').forEach(function(p) {
                        p.style.display = 'none';
                    });
                }
            });
        });
    }
})();
