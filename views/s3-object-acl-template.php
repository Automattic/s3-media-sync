<select name="s3_media_sync_settings[object_acl]">
<option<?php selected($value, 'private', true); ?>><?php _e('private', 's3-media-sync'); ?></option>
<option<?php selected($value, 'public-read', true); ?>><?php _e('public-read', 's3-media-sync'); ?></option>
</select> 
