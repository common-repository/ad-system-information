<?php

// Don't load directly
if (!defined('ABSPATH')) {
   die('-1');
}

// Sys. Info page

?>
		<div class="wrap">
				<h2><?php _e( 'System Information', 'wp-asi' ); ?></h2><br/>
				<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=asi-system-info' ) ); ?>" method="post" dir="ltr">
						<textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="edd-sysinfo" >
<?php

asi_sys_info();
?>
</textarea>
						<p class="submit">
<a href="?page=adpress-asi&action=download" class="button-primary">Downloads System Information File</a><br />
</p>
<p class="submit">
<a href="?page=adpress-asi&action=reinstall" class="button-primary">Re-Install AdPress</a>
					</p>
				</form>
				</div>
		</div>

