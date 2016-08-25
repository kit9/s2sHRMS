<div id="menu" class="hidden-print hidden-xs">
    <div class="sidebar sidebar-inverse">
        <div class="user-profile media innerAll">
            <img src="<?php echo $con->baseUrl("assets/images/rpac_logo_2.png"); ?>" alt="" height="60px" width="190px">
        </div>
        <div class="sidebarMenuWrapper">
            <ul class="list-unstyled">
                    <li class="active"><a href="<?php echo $con->baseUrl("view/dashboard/index.php"); ?>"><i class=" icon-projector-screen-line"></i><span>Dashboard</span></a></li>
                    <li><a href="<?php echo $con->baseUrl("view/apply_leave/index.php"); ?>"><i class=" icon-projector-screen-line"></i><span>Annual Leave Apply</span></a></li>
                    <li class="hasSubmenu">
                        <a href="#" data-target="#menu-style" data-toggle="collapse"><i class="icon-compose"></i><span>Employee</span></a>
                        <ul class="collapse" id="menu-style">
                        <!--Navigation Menu :: Manage Hotel-->
                            <li><a href="<?php echo $con->baseUrl("view/employee/add.php"); ?>">Add Employee</a></li>
                            <li><a href="<?php echo $con->baseUrl("view/employee/index.php"); ?>">All Employee</a></li>   
                        </ul>
                    </li>
                    
                    <li class="hasSubmenu">
                        <a href="#" data-target="#annual" data-toggle="collapse"><i class="icon-compose"></i><span>Annual Leave</span></a>
                        <ul class="collapse" id="annual">
                        <!--Navigation Menu :: Manage Hotel-->
                            <li><a href="<?php echo $con->baseUrl("view/annual_leave/index.php");?>">Calendar</a></li>
                            <li><a href="<?php echo $con->baseUrl("view/annual_leave/add.php"); ?>">Add a Schedule</a></li>   
                        </ul>
                    </li>
                    
                     <li class="hasSubmenu">
                        <a href="<?php echo $con->baseUrl("view/report/index.php"); ?>" data-target="#shop"><i class="icon-compose"></i>Report</a>
                    </li>
          
                    <li class="hasSubmenu">
                        <a href="#" data-target="#account" data-toggle="collapse"><i class="icon-shopping-cart"></i><span>Payroll</span></a>
                        <ul class="collapse" id="account">
                            <!--Navigation Menu :: Manage Hotel-->
                            <li><a href="<?php echo $con->baseUrl("view/payroll/search_employee.php"); ?>">Add Salary</a></li>     
                        </ul>
                    </li>

                    <li class="hasSubmenu">
                        <a href="#" data-target="#shop" data-toggle="collapse"><i class="icon-compose"></i>Pay Slip</a>
                        <ul class="collapse" id="shop">
                            <li><a href="<?php echo $con->baseUrl("view/payslip/search_employee.php"); ?>">Generate Pay Slip</a></li>
                        </ul>
                    </li
                    
                    
                    

            </ul>
        </div>
    </div>
</div>
<div id="content" style="background-color: white;">
    <div class="innerAll spacing-x2" style="background-color: white;">

