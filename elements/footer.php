<?php require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../config/config1.php"); ?>
<footer class="footer font-small bg-dark py-4">
  <div class="container text-center text-md-left">
    <div class="row">
      <div class="col-md-6 mt-md-0 mt-3">
        <p class="text-secondary"><?php echo $name; ?></p>
      </div>
    </div>
  </div>

  <div class="footer-copyright text-center py-1">
      <p class="text-secondary"><?php echo $footertext; ?></p>
    <p class="text-secondary"><?php echo $projectandversion; ?></p>
  </div>

</footer>
