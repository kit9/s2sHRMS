<?php if (!empty($msg)): ?>
    <div class="alert alert-success fade in">
        <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<?php if (!empty($err)): ?>
<div class="alert alert-success fade in" style="color:red; background-color: lightyellow;">
        <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo $err; ?>
  </div>
<?php endif; ?>