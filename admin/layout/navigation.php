<?php
error_reporting(0);
$emp_code_nav = $_SESSION["emp_code_nav"];
$query = "SELECT * FROM module_permission WHERE emp_code='$emp_code_nav' GROUP BY module";
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
            <ul class="list-unstyled">
                <?php if (count($arr) > 0): ?>

                    <?php foreach ($arr as $data): ?>
                        <?php if ($data->module != ''): ?>
                            <li class = "hasSubmenu">
                                <a href = "#" data-target = "#menu-style_<?php echo $data->permission_id; ?>" data-toggle = "collapse"><i class = "icon-compose"></i><span><?php echo $data->module; ?></span></a>
                                <ul class = "collapse" id = "menu-style_<?php echo $data->permission_id; ?>">
                                    <!--Navigation Menu :: Manage Hotel-->
                                    <?php $query = $con->SelectAllByCondition("module_permission", "module='$data->module' AND emp_code='$data->emp_code'"); ?>
                                    <?php foreach ($query as $data): ?>
                                        <li><a href="<?php echo $con->baseUrl("view/" . $data->base_url); ?>?permission_id=<?php echo $data->permission_id; ?>"><i class=" icon-projector-screen-line"></i><span><?php echo $data->module_page_title; ?></span></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php
                     $query_two = "SELECT ra.em_role_id, mp.* FROM role_assign AS ra
                    LEFT JOIN module_permission AS mp ON mp.em_role_id = ra.em_role_id
                    WHERE ra.emp_code = '$emp_code_nav' AND ra.em_role_id > '0' GROUP BY mp.module";
                    $result_two = mysqli_query($open, $query_two);
                    while ($rows_two = mysqli_fetch_object($result_two)) {
                        $arr_two[] = $rows_two;
                    }

             
                    ?>
                    <?php if (count($arr_two) > 0): ?>

                      

                        <?php foreach ($arr_two as $data): ?>
                            <?php if ($data->module != ''): ?>
                                <li class = "hasSubmenu">
                                    <a href = "#" data-target = "#menu-style_<?php echo $data->permission_id; ?>" data-toggle = "collapse"><i class = "icon-compose"></i><span><?php echo $data->module; ?></span></a>
                                    <ul class = "collapse" id = "menu-style_<?php echo $data->permission_id; ?>">
                                        <!--Navigation Menu :: Manage Hotel-->
                                        <?php $query = $con->SelectAllByCondition("module_permission", "module='$data->module' AND em_role_id='$data->em_role_id'"); ?>
                                        <?php foreach ($query as $data): ?>
                                            <li><a href="<?php echo $con->baseUrl("view/" . $data->base_url); ?>?permission_id=<?php echo $data->permission_id; ?>"><i class=" icon-projector-screen-line"></i><span><?php echo $data->module_page_title; ?></span></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<div id="content" style="background-color: white;"> <div class="innerAll spacing-x2" style="background-color: white;">

