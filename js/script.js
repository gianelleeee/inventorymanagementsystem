var sideBarIsOpen = true;

    toggleBtn.addEventListener('click', (event) =>{
        event.preventDefault();

        if(sideBarIsOpen){
            dashboard_sidebar.style.width = '10%';
            dashboard_sidebar.style.transition = '0.5s all'
            dashboard_content_container.style.width = '90%';
            dashboard_logo.style.fontSize = '60px';
            // userImage.style.width = '60px';

            menuIcons = document.getElementsByClassName('menuText');
            for(var i=0; i < menuIcons.length; i++){
                menuIcons[i].style.display = 'none';
            }

            menuIcons = document.getElementsByClassName('welcome_user');
            for(var i=0; i < menuIcons.length; i++){
                menuIcons[i].style.display = 'none';
            }

            document.getElementsByClassName('dashboard_menu_lists')[0].style.textAlign = 'center';
            sideBarIsOpen = false;
        } else{
            dashboard_sidebar.style.width = '20%';
            dashboard_content_container.style.width = '80%';
            dashboard_logo.style.fontSize = '80px';
            // userImage.style.width = '80px';

            menuIcons = document.getElementsByClassName('menuText');
            for(var i=0; i < menuIcons.length; i++){
                menuIcons[i].style.display = 'inline-block';
            }

            menuIcons = document.getElementsByClassName('welcome_user');
            for(var i=0; i < menuIcons.length; i++){
                menuIcons[i].style.display = 'inline-block';
            }

            document.getElementsByClassName('dashboard_menu_lists')[0].style.textAlign = 'left';
            sideBarIsOpen = true;
        }
        
    });

    // submenu show/hide function
    document.addEventListener('click', function(e){
        let clickedE1 = e.target;

        if(clickedE1.classList.contains('showHideSideMenu')){
            let targetMenu = clickedE1.dataset.submenu;

            //check if there is submenu
            if(targetMenu != undefined){
               let subMenu =  document.getElementById(targetMenu);
               
               if(subMenu.style.display === 'block') subMenu.style.height = 'none';
               else subMenu.style.display = 'block';
            }
        }
            
    });