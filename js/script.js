// Function to show/hide submenu
function showHideSubMenu(subMenu, mainMenuIcon) {
    if (subMenu !== null) {
        if (subMenu.style.display === 'block') {
            subMenu.style.display = 'none';
            mainMenuIcon.classList.remove('fa-angle-down');
            mainMenuIcon.classList.add('fa-angle-left');
        } else {
            subMenu.style.display = 'block';
            mainMenuIcon.classList.remove('fa-angle-left');
            mainMenuIcon.classList.add('fa-angle-down');
        }
    }
}

// Ensure elements are selected properly
const toggleBtn = document.getElementById('toggleBtn');
const dashboard_sidebar = document.getElementById('dashboard_sidebar');
const dashboard_content_container = document.getElementById('dashboard_content_container');
const dashboard_logo = document.getElementById('dashboard_logo');

// Sidebar toggle button functionality
let sideBarIsOpen = true;

toggleBtn.addEventListener('click', (event) => {
    event.preventDefault();

    if (sideBarIsOpen) {
        dashboard_sidebar.style.width = '10%';
        dashboard_sidebar.style.transition = '0.5s all';
        dashboard_content_container.style.width = '90%';
        dashboard_logo.style.fontSize = '60px';

        let menuIcons = document.querySelectorAll('.menuText, .welcome_user');
        menuIcons.forEach(icon => icon.style.display = 'none');

        document.querySelector('.dashboard_menu_lists').style.textAlign = 'center';
        sideBarIsOpen = false;
    } else {
        dashboard_sidebar.style.width = '20%';
        dashboard_content_container.style.width = '80%';
        dashboard_logo.style.fontSize = '80px';

        let menuIcons = document.querySelectorAll('.menuText, .welcome_user');
        menuIcons.forEach(icon => icon.style.display = 'inline-block');

        document.querySelector('.dashboard_menu_lists').style.textAlign = 'left';
        sideBarIsOpen = true;
    }
});

// Submenu show/hide function
document.addEventListener('click', function (e) {
    let clickedEl = e.target;

    if (clickedEl.classList.contains('showHideSubMenu')) {
        let subMenu = clickedEl.closest('li').querySelector('.subMenus');
        let mainMenuIcon = clickedEl.closest('li').querySelector('.mainMenuIconArrow');

        document.querySelectorAll('.subMenus').forEach(sub => {
            if (subMenu !== sub) sub.style.display = 'none';
        });

        showHideSubMenu(subMenu, mainMenuIcon);
    }
});

// Add or hide active class in dashboard
let pathArray = window.location.pathname.split('/');
let curFile = pathArray[pathArray.length - 1];

let curNav = document.querySelector(`a[href="./${curFile}"]`);
if (curNav) {
    curNav.classList.add('subMenuActive');

    let mainNav = curNav.closest('li.liMainMenu');
    if (mainNav) {
        mainNav.style.background = '#649037';
    }

    if (curFile === 'dashboard.html') { // Replace with your actual Dashboard file name
        curNav.style.borderTop = 'none';
        curNav.style.borderBottom = 'none';
    }

    let subMenu = curNav.closest('.subMenus');
    let mainMenuIcon = mainNav.querySelector('i.mainMenuIconArrow');
    if (subMenu && mainMenuIcon) {
        showHideSubMenu(subMenu, mainMenuIcon);
    }
}

