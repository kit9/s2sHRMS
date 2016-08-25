<?php
session_start();
include ('../config/class.config.php');
$con = new Config();
$open = $con->open();
$emp_code = $_SESSION["emp_code"];
$query = "SELECT * FROM module_permission WHERE emp_code='RPAC0537' GROUP BY module";
$result = mysqli_query($open, $query);
while ($rows = mysqli_fetch_object($result)) {
    $arr[] = $rows;
}
?>
<div id="menu" class="hidden-print hidden-xs">
    <div class="sidebar sidebar-inverse">
        <div class="user-profile media innerAll">
            <img style="margin-top: 20px;" src="<?php echo $con->baseUrl("assets/images/rpac_logo_2.png"); ?>" alt="" height="60px" width="190px">
        </div>
        <div class="sidebarMenuWrapper" style="margin-top: 20px;">
            <?php foreach ($arr as $data): ?>
                <?php if ($data->module != ''): ?>
                    <li class = "hasSubmenu">
                        <a href = "#" data-target = "#menu-style_<?php echo $data->module; ?>" data-toggle = "collapse"><i class = "icon-compose"></i><span><?php echo $data->module; ?></span></a>
                        <ul class = "collapse" id = "menu-style_<?php echo $data->module; ?>">
                            <!--Navigation Menu :: Manage Hotel-->
                            <?php $query = $con->SelectAllByCondition("module_permission", "module='$data->module'"); ?>
                            <?php foreach ($query as $data): ?>
                                <li><a href="<?php echo $con->baseUrl("view/" . $data->base_url); ?>"><i class=" icon-projector-screen-line"></i><span><?php echo $data->module_page_title; ?></span></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div id="content" style="background-color: white;">
<div class="innerAll spacing-x2" style="background-color: white;">

