
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
                        <a href="./dashboard.php"><i class="fa fa-dashboard"></i><span class="menuText">   Dashboard</span></a>
                    </li>
                    <li class="liMainMenu">
                        <a href="javascript:void(0);" class="showHideSubMenu">
                            <i class="fa fa-tag showHideSubMenu"></i> 
                            <span class="menuText showHideSubMenu"> Product Management</span>
                            <i class="fa fa-angle-left mainMenuIconArrow showHideSubMenu"></i>
                        </a>
                        <ul class="subMenus">
                            <li> <a class="subMenuLink" href="./products-view.php"><i class="fa-regular fa-circle circle"></i> View Product</a> </li>
                            <li> <a class="subMenuLink" href="./products-add.php"><i class="fa-regular fa-circle circle"></i> Add Product</a> </li>
                            <li> <a class="subMenuLink" href="./products-order.php"><i class="fa-regular fa-circle circle"></i> Order Product</a> </li>
                        </ul>
                    </li>
                    <li class="liMainMenu">
                        <a href="javascript:void(0);" class="showHideSubMenu">
                            <i class="fa fa-barcode showHideSubMenu"></i> 
                            <span class="menuText showHideSubMenu"> SKU Management</span>
                            <i class="fa fa-angle-left mainMenuIconArrow showHideSubMenu"></i>
                        </a>
                        <ul class="subMenus">
                            <li> <a class="subMenuLink" href="./sku-view.php"><i class="fa-regular fa-circle circle"></i> View SKU</a> </li>
                            <li> <a class="subMenuLink" href="./sku-add.php"><i class="fa-regular fa-circle circle"></i> Add SKU</a> </li>
                        </ul>
                    </li>
                    <li class="liMainMenu">
                        <a href="javascript:void(0);" class="showHideSubMenu">
                            <i class="fa fa-dollar-sign showHideSubMenu"></i> 
                            <span class="menuText showHideSubMenu"> Stocks Management</span>
                            <i class="fa fa-angle-left mainMenuIconArrow showHideSubMenu"></i>
                        </a>
                        <ul class="subMenus">
                            <li> <a class="subMenuLink" href="#"><i class="fa-regular fa-circle circle"></i> View Sales</a> </li>
                            <li> <a class="subMenuLink" href="#"><i class="fa-regular fa-circle circle"></i> Add Sales</a> </li>
                        </ul>
                    </li>
                    <li class="liMainMenu showHideSubMenu">
                        <a href="javascript:void(0);" class="showHideSubMenu">
                            <i class="fa fa-user-plus showHideSubMenu"></i> 
                            <span class="menuText showHideSubMenu"> User Management</span>
                            <i class="fa fa-angle-left mainMenuIconArrow showHideSubMenu"></i>
                        </a>
                        <ul class="subMenus">
                            <li> <a class="subMenuLink" href="./users.view.php"><i class="fa-regular fa-circle circle"></i> View Users</a> </li>
                            <li> <a class="subMenuLink" href="./users.add.php"><i class="fa-regular fa-circle circle"></i> Add Users</a> </li>
                        </ul>
                    </li>
                   
                    
                </ul>
            </div>
        </div>

        