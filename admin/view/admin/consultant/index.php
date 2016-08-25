<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
$err = "";
$msg = '';

$consultants = $con->SelectAll("tbl_consultant");

?>
<?php include '../view_layout/header_view.php'; ?>

<div class="k-toolbar k-grid-toolbar">
    <a class="k-button k-button-icontext k-grid-add" href="add.php">
<span class="k-icon k-add"></span>
Add Consultant
</a>
</div>
 <div id="example" class="k-content">
            <table id="grid">
                <colgroup>
                    <col style="width:130px"/>
                    <col style="width:150px" />
                    <col style="width:150px" />
                    <col style="width:140px" />
                    <col style="width:100px" />
                    <col style="width:320px" />
                    
                </colgroup>
                <thead>
                    <tr>
                         <th data-field="c_f_name">First Name</th>
                      <th data-field="c_pres_addr">Present Address</th>
                       <th data-field="compamy_name">Company Name</th>
                        <th data-field="company_web">Company Web</th>
                        <th data-field="company_image">Image</th>
                        <th data-field="action">Action</th>
                     </tr>
                </thead>
                <tbody>
                   <?php if(count($consultants)>=1):?>
                    <?php foreach ($consultants as $cn): ?>
                    <tr>
                         <td><?php echo $cn->c_f_name; ?></td>
                      <td><?php echo $cn->c_pres_addr; ?></td>
                       <td><?php echo $cn->compamy_name; ?></td>
                        <td><?php echo $cn->company_web; ?></td>
                        <td><img src="<?php echo $con->baseUrl("uploads/consultant/consultant_image/".$cn->consultant_image);?>" height="50px" width="90px"/></td>
                         <td role="gridcell">
                             <a class="k-button k-button-icontext k-grid-edit" href="edit.php?id=<?php echo ($cn->c_id) ?>">
                                
                        <span class="k-icon k-edit"></span>
                        Edit
                        </a>
                        <a class="k-button k-button-icontext k-grid-delete" href="#">
                        <span class="k-icon k-delete"></span>
                        Delete
                        </a>
                             <a class="k-button k-button-icontext k-grid-edit" href="view.php?id=<?php echo ($cn->c_id) ?>">
                            
                        <span class="k-icon k-edit"></span>
                        Details
                        </a>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                      <?php endif;?> 
                      
                   </tbody>
            </table>

            <script>
                $(document).ready(function() {
                    $("#grid").kendoGrid({
                pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSize: 5,
                pageSizes: true,
                pageSizes: [5, 10, 20, 50],
            },
          
            sortable: true,
            groupable: true
             });
                });
                
            </script>
        </div>
<?php include '../view_layout/footer_view.php'; ?>




