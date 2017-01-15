<?php
	list($title, $link) = jm_get_cannot_view_resume_message( $resume_id );
?>
<article id="post-<?php $resume_id; ?>" class="resume">
	<h3><?php echo $title; ?></h3>
	<?php if( !empty( $link ) ) echo $link; ?>
</article>
