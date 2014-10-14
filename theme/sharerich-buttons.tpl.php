<div class="sharerich-wrapper">
<?php if ($title) : ?>
<h4><?php print $title; ?></h4>
<?php endif; ?>
<?php if ($item_list) : ?>
<?php print render($item_list); ?>
<?php endif; ?>
</div>