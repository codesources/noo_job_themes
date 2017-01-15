<?php

require_once NOO_FRAMEWORK . '/job/functions.php';
require_once NOO_FRAMEWORK . '/job/init.php';
if ( is_admin() ) {
	require_once NOO_FRAMEWORK . '/job/admin.php';
	require_once NOO_FRAMEWORK . '/job/admin-settings.php';
	require_once NOO_FRAMEWORK . '/job/admin-job-list.php';
	require_once NOO_FRAMEWORK . '/job/admin-job-edit.php';
}
require_once NOO_FRAMEWORK . '/job/job_type.php';
require_once NOO_FRAMEWORK . '/job/job_location.php';
require_once NOO_FRAMEWORK . '/job/job-default-fields.php';
require_once NOO_FRAMEWORK . '/job/job-custom-fields.php';
require_once NOO_FRAMEWORK . '/job/job-custom-fields-package.php';
require_once NOO_FRAMEWORK . '/job/job-expired.php';
require_once NOO_FRAMEWORK . '/job/job-bookmark.php';
require_once NOO_FRAMEWORK . '/job/job-query.php';
require_once NOO_FRAMEWORK . '/job/job-enqueue.php';
require_once NOO_FRAMEWORK . '/job/job-posting.php';
require_once NOO_FRAMEWORK . '/job/job-posting-free.php';
require_once NOO_FRAMEWORK . '/job/job-posting-package.php';
require_once NOO_FRAMEWORK . '/job/job-posting-action.php';
require_once NOO_FRAMEWORK . '/job/job-viewable.php';
require_once NOO_FRAMEWORK . '/job/job-viewable-resume-package.php';
require_once NOO_FRAMEWORK . '/job/job-apply-action.php';
require_once NOO_FRAMEWORK . '/job/job-apply-resume-package.php';
require_once NOO_FRAMEWORK . '/job/job-template.php';
require_once NOO_FRAMEWORK . '/job/job-template-shortcodes.php';
require_once NOO_FRAMEWORK . '/job/job-template-schema.php';
require_once NOO_FRAMEWORK . '/job/extra.php';
require_once NOO_FRAMEWORK_ADMIN . '/noo_job.php';
