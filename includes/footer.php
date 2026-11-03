</main>
<footer class="foot wrap">ExamPortal &middot; Online Examination System &middot; MCA mini project</footer>

<dialog id="confirm-dialog" aria-labelledby="confirm-title">
  <form method="dialog">
    <h2 id="confirm-title">Please confirm</h2>
    <p id="confirm-text"></p>
    <div class="dialog-actions">
      <button class="btn btn-secondary" value="cancel">Cancel</button>
      <button class="btn" id="confirm-yes" value="yes">Confirm</button>
    </div>
  </form>
</dialog>
<script src="<?= url('assets/js/app.js') ?>"></script>
<?php if (!empty($page_js)): ?><script src="<?= url('assets/js/' . $page_js) ?>"></script><?php endif; ?>
</body>
</html>
