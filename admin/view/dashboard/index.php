<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
$leaves = array();

//Collecting leave data
$annual_leaves = "SELECT      
         l.start_date, l.end_date, 
         e.emp_id, e.emp_firstname, e.emp_lastname,
         e.emp_code, a.app_start_date, a.app_end_date FROM annual_leave l, 
         employee e, applied_annual_leave a WHERE e.emp_id=l.emp_id AND a.emp_id=l.emp_id";
$result = mysqli_query($open, $annual_leaves);
if(!empty($result))
{
while ($rows = mysqli_fetch_object($result)) {
    $leaves[] = $rows;
}
}
?>

<?php include '../view_layout/header_view.php'; ?>
<script src="../assets/components/library/jquery/jquery.min.js?v=v1.2.3"></script>
<script src="../assets/components/library/jquery/jquery-migrate.min.js?v=v1.2.3"></script>
<script src="../assets/components/library/modernizr/modernizr.js?v=v1.2.3"></script>
<script src="../assets/components/plugins/less-js/less.min.js?v=v1.2.3"></script>
<script src="../assets/components/modules/admin/charts/flot/assets/lib/excanvas.js?v=v1.2.3"></script>
<script src="../assets/components/plugins/browser/ie/ie.prototype.polyfill.js?v=v1.2.3"></script>
<!-- Widget -->


<div class="col-md-6">

    <!-- Widget -->
    <div class="widget widget-inverse">

        <!-- Widget heading -->
        <div class="widget-head">
            <h4 class="heading">Attendance chart</h4>
        </div>
        <!-- // Widget heading END -->

        <div class="widget-body">
            <!-- Simple Chart -->
            <div id="chart_simple" class="flotchart-holder"></div>
        </div>
    </div>
    <!-- // Widget END -->

</div>

<div class="col-md-6">

    <!-- Widget -->
    <div class="widget widget-inverse">

        <!-- Widget heading -->
        <div class="widget-head">
            <h4 class="heading">Leave Chart</h4>
        </div>
        <!-- // Widget heading END -->

        <div class="widget-body">

            <!-- Stacked bars Chart -->
            <div id="chart_stacked_bars" class="flotchart-holder"></div>




        </div>
    </div>
    <!-- // Widget END -->

</div>
<div style="clear:both;"></div>
<div class="widget widget-inverse">

    <!-- Widget heading -->
    <div class="widget-head">
        <h4 class="heading">Employment Chart</h4>
    </div>
    <!-- // Widget heading END -->

    <div class="widget-body">

        <!-- Pie Chart -->
        <div id="chart_pie" class="flotchart-holder"></div>





    </div>
</div>
<div class="clearfix"></div>
<!-- Widget -->

<div style="clear:both;"></div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>


<!-- Global -->
<script>
    var basePath = '',
            commonPath = '../assets/', rootPath = '../',
            DEV = false,
            componentsPath = '../assets/components/';

    var primaryColor = '#cb4040',
            dangerColor = '#b55151',
            infoColor = '#466baf',
            successColor = '#8baf46',
            warningColor = '#ab7a4b',
            inverseColor = '#45484d';

    var themerPrimaryColor = primaryColor;
</script>

<!--<script src="../../assets/components/library/bootstrap/js/bootstrap.min.js?v=v1.2.3"></script>-->
<script src="../../assets/components/plugins/nicescroll/jquery.nicescroll.min.js?v=v1.2.3"></script>
<script src="../../assets/components/plugins/breakpoints/breakpoints.js?v=v1.2.3"></script>
<script src="../../assets/components/core/js/animations.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/jquery.flot.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/jquery.flot.resize.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/plugins/jquery.flot.tooltip.min.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotcharts.common.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-simple.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-line.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/plugins/jquery.flot.orderBars.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-bars-ordered.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-bars-stacked.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/jquery.flot.pie.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-donut.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-pie.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-bars-horizontal.init.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/custom/js/flotchart-autoupdating.init.js?v=v1.2.3"></script>
<script src="../../assets/components/plugins/holder/holder.js?v=v1.2.3"></script>
<script src="../../assets/components/core/js/sidebar.main.init.js?v=v1.2.3"></script>
<script src="../../assets/components/core/js/sidebar.collapse.init.js?v=v1.2.3"></script>
<script src="../../assets/components/helpers/themer/assets/plugins/cookie/jquery.cookie.js?v=v1.2.3"></script>
<script src="../../assets/components/core/js/core.init.js?v=v1.2.3"></script>	
