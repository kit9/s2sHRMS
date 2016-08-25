<?php
$full_name = '';
$emp_id = '';
$emp_cod = $_SESSION['emp_code'];
$users = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_cod'");
foreach ($users as $user) {
    $emp_id = $user->emp_id;
    $full_name = $user->emp_firstname;
}
?>
<div style="min-height: 74px; border-color: #0866c6;" class="navbar navbar-fixed-top navbar-primary main" role="navigation">
    <div class="navbar-header pull-left">
        <div style="margin-top: 12px;" class="navbar-brand">
            <div class="pull-left">
                <a href="" class="toggle-button toggle-sidebar btn-navbar"><i class="fa fa-bars"></i></a>
            </div>
            <a href="" class="appbrand innerL">S2S PAYROLL</a>
        </div>
    </div>


    <div class="navbar-header pull-right">

        <ul style="margin-left:-20%;">   
            <li class="dropdown notification hidden-sm hidden-md">
                <h5 style="color: white; margin-top: 15px;"> Welcome, <?php echo $full_name; ?> </h5>
<!--                <img style="height: 50px; width: 80px; margin-top: -38px; border: 2px solid #3985D1;" src="<?php //echo $con->baseUrl("uploads/admin/$user_img");    ?>" class="edit_profile_img">-->
                <br />
                <h5 style="margin-top: -20px; margin-left: -5px; " href="#" class="dropdown-toggle hovclne" data-toggle="dropdown"><span> &nbsp;</span>Account<span class="caret"></span></h5>
                <div class="clearfix"></div>
                <ul class="dropdown-menu">
                    <li><a href="../employee/details.php?emp_id=<?php echo $emp_id;?>">Profile</a></li>
                    <li>
                        <a style="margin-left: -5px; height: 30px;">
                            <form method="post">
                                <input type="submit" style="border-style: none; background: none;" name="btnLogout" value="Logout">
                            </form>

                        </a>
                    </li>
                </ul>
            </li>    
        </ul>
        <br />
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="clearfix"></div>