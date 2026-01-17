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
        // Remove old Login/Logout/Admin links
        const existingLogin = nav.querySelector('li a[href="login.html"]');
        const existingLogout = nav.querySelector('li a[onclick*="logout"]');
        const existingAdmin = nav.querySelector('li a[href="admin.php"]');
        
        if (existingLogin) existingLogin.closest('li').remove();
        if (existingLogout) existingLogout.closest('li').remove();
        if (existingAdmin) existingAdmin.closest('li').remove();
        
        // Add new links based on status
        if (currentUser) {
            // User logged in
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
    });
}

// Handle logout
async function handleLogout() {
    if (!confirm('Are you sure you want to logout?')) {
        return;
    }
    
    try {
        const response = await fetch('logout.php');
        const data = await response.json();
        
        if (data.success) {
            currentUser = null;
            isAdminUser = false;
            updateNavigation();
            window.location.href = 'index.html';
        } else {
            alert('Logout error');
        }
    } catch (error) {
        console.error('Error:', error);
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
