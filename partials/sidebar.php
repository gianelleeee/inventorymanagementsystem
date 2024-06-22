
        <div class="dashboard_sidebar" id="dashboard_sidebar">
            <h3 class="dashboard_logo" id="dashboard_logo">IMS</h1>
            <div class="dashboard_sidebar_user">
                <!-- <img src="images/users/testuser.png" alt="user image" id="userImage"/> -->
                <p class="welcome_user">Welcome Back!</p>
                <span><?= $user['first_name'] . ' ' .$user['last_name'] ?></span>
            </div>
            <div class="dashboard_sidebar_menus">
                <ul class="dashboard_menu_lists">
                    <!--  class="menuActive" -->
                    <li class="liMainMenu">
                        <a href="./dashboard.php"><i class="fa fa-dashboard"></i><span class="menuText"> Dashboard</span></a>
                    </li>
                    <li class="liMainMenu">
                        <a href=""><i class="fa fa-user-plus"></i> <span class="menuText"> Product Management</span></a>
                    </li>
                    <li class="liMainMenu">
                        <a href=""><i class="fa fa-user-plus"></i> <span class="menuText"> SKU Management</span></a>
                    </li>
                    <li class="liMainMenu showHideSideMenu">
                        <a href="javascript:void(0);" class="showHideSideMenu">
                            <i class="fa fa-user-plus showHideSideMenu"></i> 
                            <span class="menuText showHideSideMenu"> User Management</span>
                            <i class="fa fa-angle-left mainMenuIconArrow showHideSideMenu"></i>
                        </a>
                        <ul class="subMenus">
                            <li> <a class="subMenuLink" href="#"><i class="fa-regular fa-circle circle"></i> View Users</a> </li>
                            <li> <a class="subMenuLink" href="#"><i class="fa-regular fa-circle circle"></i> Add Users</a> </li>
                        </ul>
                    </li>
                   
                    
                </ul>
            </div>
        </div>

        