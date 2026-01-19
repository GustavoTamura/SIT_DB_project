// JavaScript file to handle authentication and navigation

let currentUser = null;
let isAdminUser = false;

// Check session on load
async function checkSession() {
    try {
        const response = await fetch('check_session.php');
        const data = await response.json();
        
        if (data.logged_in) {
            currentUser = data.user;
            isAdminUser = data.user.is_admin === true;
            updateNavigation();
        } else {
            currentUser = null;
            isAdminUser = false;
            updateNavigation();
        }
    } catch (error) {
        console.error('Error checking session:', error);
        currentUser = null;
        isAdminUser = false;
        updateNavigation();
    }
}

// Update navigation based on login status
function updateNavigation() {
    const navLinks = document.querySelectorAll('.nav-links');
    
    navLinks.forEach(nav => {
        // Remove all old Login/Logout/Admin/Bookings links (use querySelectorAll to remove all instances)
        nav.querySelectorAll('li a[href="login.html"]').forEach(el => el.closest('li').remove());
        nav.querySelectorAll('li a[onclick*="Logout"]').forEach(el => el.closest('li').remove());
        nav.querySelectorAll('li a[href="admin.php"]').forEach(el => el.closest('li').remove());
        nav.querySelectorAll('li a[href="booking_history.html"]').forEach(el => el.closest('li').remove());
        
        // Add new links based on status
        if (currentUser) {
            // User logged in
            // Add My Bookings link
            const bookingsLi = document.createElement('li');
            bookingsLi.innerHTML = '<a href="booking_history.html">My Bookings</a>';
            nav.appendChild(bookingsLi);
            
            if (isAdminUser) {
                // Add Admin link
                const adminLi = document.createElement('li');
                adminLi.innerHTML = '<a href="admin.php">Admin</a>';
                nav.appendChild(adminLi);
            }
            
            // Add Logout link
            const logoutLi = document.createElement('li');
            logoutLi.innerHTML = `<a href="#" onclick="handleLogout(); return false;">Logout (${escapeHtml(currentUser.name)})</a>`;
            nav.appendChild(logoutLi);
        } else {
            // User not logged in - add Login link
            const loginLi = document.createElement('li');
            loginLi.innerHTML = '<a href="login.html">Login</a>';
            nav.appendChild(loginLi);
        }
        
        // Mark current page as active
        markActiveLink(nav);
    });
}

// Mark the current page link as active
function markActiveLink(nav) {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const links = nav.querySelectorAll('a');
    
    links.forEach(link => {
        link.classList.remove('active');
        const linkHref = link.getAttribute('href');
        
        if (linkHref) {
            const linkPage = linkHref.split('/').pop().split('#')[0];
            if (linkPage === currentPage || 
                (currentPage === '' && linkPage === 'index.html')) {
                link.classList.add('active');
            }
        }
    });
}

// Handle logout
function handleLogout() {
    showLogoutConfirmation();
}

// Show custom logout confirmation dialog
function showLogoutConfirmation() {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    `;
    
    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        max-width: 400px;
        width: 90%;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: slideIn 0.3s ease;
    `;
    
    modalContent.innerHTML = `
        <h3 style="margin: 0 0 15px 0; color: #e50914; font-size: 1.5rem;">Confirm Logout</h3>
        <p style="margin: 0 0 25px 0; color: #b3b3b3; font-size: 1rem;">Are you sure you want to logout?</p>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button id="logout-cancel" style="
                padding: 10px 20px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: transparent;
                color: white;
                border-radius: 6px;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.3s;
            ">Cancel</button>
            <button id="logout-confirm" style="
                padding: 10px 20px;
                border: none;
                background: #e50914;
                color: white;
                border-radius: 6px;
                cursor: pointer;
                font-size: 1rem;
                font-weight: 600;
                transition: all 0.3s;
            ">Confirm</button>
        </div>
    `;
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        #logout-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        #logout-confirm:hover {
            background: #c40812;
            transform: scale(1.05);
        }
    `;
    document.head.appendChild(style);
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Handle cancel
    document.getElementById('logout-cancel').addEventListener('click', () => {
        modal.style.animation = 'fadeOut 0.2s ease';
        setTimeout(() => modal.remove(), 200);
    });
    
    // Handle confirm
    document.getElementById('logout-confirm').addEventListener('click', async () => {
        modal.remove();
        await performLogout();
    });
    
    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.animation = 'fadeOut 0.2s ease';
            setTimeout(() => modal.remove(), 200);
        }
    });
}

// Perform actual logout
async function performLogout() {
    try {
        const response = await fetch('logout.php');
        const data = await response.json();
        
        if (data.success) {
            // Clear client-side state
            currentUser = null;
            isAdminUser = false;
            
            // Update navigation immediately
            updateNavigation();
            
            // Redirect to home page
            window.location.href = 'index.html';
        } else {
            alert('Logout failed. Please try again.');
        }
    } catch (error) {
        console.error('Logout error:', error);
        alert('Server connection error');
    }
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check session on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkSession);
} else {
    checkSession();
}
