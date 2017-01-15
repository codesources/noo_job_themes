<?php
	list($title, $link) = jm_get_cannot_view_job_message( $job_id );
?>
<strong><?php echo $title; ?></strong>
<div id="post-<?php echo $job_id; ?>" class="cannot-view-job">
	<?php if( !empty( $link ) ) echo $link; ?>
</div>
