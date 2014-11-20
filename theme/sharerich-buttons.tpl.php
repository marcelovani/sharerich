<div class="sharerich-wrapper">
  <?php if ($title) : ?>
    <h4><?php print $title; ?></h4>
  <?php endif; ?>
  <?php if ($buttons) : ?>
    <?php print render($buttons); ?>
  <?php endif; ?>
</div>
