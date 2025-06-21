document.addEventListener('DOMContentLoaded', function() {
    const hideButton = document.getElementById('hide-sidebar');
    const sidebar = document.querySelector('.left_area');
    const body = document.body;

    if (hideButton && sidebar) {
        hideButton.addEventListener('click', function() {
            body.classList.toggle('sidebar-hidden');
            
            if (body.classList.contains('sidebar-hidden')) {
                localStorage.setItem('sidebarHidden', 'true');
            } else {
                localStorage.removeItem('sidebarHidden');
            }
        });

        if (localStorage.getItem('sidebarHidden') === 'true') {
            body.classList.add('sidebar-hidden');
        }
    }

    const jobCountElement = document.getElementById('job-count');
    if (jobCountElement) {
        const jobCount = document.querySelectorAll('.job-card').length;
        jobCountElement.textContent = jobCount;
    }
    const currentPage = location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
        
        if (currentPage === 'profile.php' && link.getAttribute('href') === 'profile.php') {
            link.classList.add('active');
        }
    });
});