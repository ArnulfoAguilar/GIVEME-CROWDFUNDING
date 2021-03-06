<?php
/**
 * @package Wsal
 * @subpackage Sensors
 * System Activity sensor.
 *
 * 6000 Events automatically pruned by system
 * 6001 Option Anyone Can Register in WordPress settings changed
 * 6002 New User Default Role changed
 * 6003 WordPress Administrator Notification email changed
 * 6004 WordPress was updated
 * 6005 User changes the WordPress Permalinks
 * 6007 User requests non-existing pages (404 Error Pages)
 * 8009 User changed forum's role
 * 8010 User changed option of a forum
 * 8012 User changed time to disallow post editing
 * 8013 User changed the forum setting posting throttle time
 * 1006 User logged out all other sessions with the same username
 * 6004 WordPress was updated
 * 6008 Enabled/Disabled the option Discourage search engines from indexing this site
 * 6009 Enabled/Disabled comments on all the website
 * 6010 Enabled/Disabled the option Comment author must fill out name and email
 * 6011 Enabled/Disabled the option Users must be logged in and registered to comment
 * 6012 Enabled/Disabled the option to automatically close comments
 * 6013 Changed the value of the option Automatically close comments
 * 6014 Enabled/Disabled the option for comments to be manually approved
 * 6015 Enabled/Disabled the option for an author to have previously approved comments for the comments to appear
 * 6016 Changed the number of links that a comment must have to be held in the queue
 * 6017 Modified the list of keywords for comments moderation
 * 6018 Modified the list of keywords for comments blacklisting
 */
class WSAL_Sensors_System extends WSAL_AbstractSensor {

    /**
     * Transient name.
     * WordPress will prefix the name with "_transient_" or "_transient_timeout_" in the options table.
     */
    const TRANSIENT_404         = 'wsal-404-attempts';
    const TRANSIENT_VISITOR_404 = 'wsal-visitor-404-attempts';

    /**
     * Listening to events using WP hooks.
     */
    public function HookEvents() {

        add_action( 'wsal_prune', array( $this, 'EventPruneEvents' ), 10, 2 );
        add_action( 'admin_init', array( $this, 'EventAdminInit' ) );

        add_action( 'automatic_updates_complete', array( $this, 'WPUpdate' ), 10, 1 );
        add_filter( 'template_redirect', array( $this, 'Event404' ) );

        $upload_dir     = wp_upload_dir();
        $uploadsDirPath = trailingslashit( $upload_dir['basedir'] ) . 'wp-security-audit-log/404s/';
        if ( ! $this->CheckDirectory( $uploadsDirPath ) ) {
            wp_mkdir_p( $uploadsDirPath );
        }

        // Directory for logged in users log files.
        $user_upload_dir    = wp_upload_dir();
        $user_upload_path   = trailingslashit( $user_upload_dir['basedir'] . '/wp-security-audit-log/404s/users/' );
        if ( ! $this->CheckDirectory( $user_upload_path ) ) {
            wp_mkdir_p( $user_upload_path );
        }

        // Directory for visitor log files.
        $visitor_upload_dir    = wp_upload_dir();
        $visitor_upload_path   = trailingslashit( $visitor_upload_dir['basedir'] . '/wp-security-audit-log/404s/visitors/' );
        if ( ! $this->CheckDirectory( $visitor_upload_path ) ) {
            wp_mkdir_p( $visitor_upload_path );
        }

        // Cron Job 404 log files pruning.
        add_action( 'log_files_pruning', array( $this, 'LogFilesPruning' ) );
        if ( ! wp_next_scheduled( 'log_files_pruning' ) ) {
            wp_schedule_event( time(), 'daily', 'log_files_pruning' );
        }
        // whitelist options.
        add_action( 'whitelist_options', array( $this, 'EventOptions' ), 10, 1 );
    }

    /**
     * @param int $count The number of deleted events.
     * @param string $query Query that selected events for deletion.
     */
    public function EventPruneEvents($count, $query)
    {
        $this->plugin->alerts->Trigger(6000, array(
            'EventCount' => $count,
            'PruneQuery' => $query,
        ));
    }

    /**
     * 404 limit count.
     * @return integer limit
     */
    protected function Get404LogLimit()
    {
        return $this->plugin->settings->Get404LogLimit();
    }

    protected function GetVisitor404LogLimit() {
        return $this->plugin->settings->GetVisitor404LogLimit();
    }

    /**
     * Expiration of the transient saved in the WP database.
     * @return integer Time until expiration in seconds from now
     */
    protected function Get404Expiration()
    {
        return 24 * 60 * 60;
    }

    /**
     * Check 404 limit.
     * @param integer $site_id blog ID
     * @param string $username username
     * @param string $ip IP address
     * @return boolean passed limit true|false
     */
    protected function IsPast404Limit($site_id, $username, $ip)
    {
        $get_fn = $this->IsMultisite() ? 'get_site_transient' : 'get_transient';
        $data = $get_fn(self::TRANSIENT_404);
        return ($data !== false) && isset($data[$site_id.":".$username.":".$ip]) && ($data[$site_id.":".$username.":".$ip] > $this->Get404LogLimit());
    }

    protected function IsPastVisitor404Limit( $site_id, $username, $ip ) {
        $get_fn = $this->IsMultisite() ? 'get_site_transient' : 'get_transient';
        $data = $get_fn( self::TRANSIENT_VISITOR_404 );
        return ( false !== $data ) && isset( $data[ $site_id . ':' . $username . ':' . $ip ] ) && ( $data[ $site_id . ':' . $username . ':' . $ip ] > $this->GetVisitor404LogLimit() );
    }

    /**
     * Increment 404 limit.
     * @param integer $site_id blog ID
     * @param string $username username
     * @param string $ip IP address
     */
    protected function Increment404($site_id, $username, $ip) {
        $get_fn = $this->IsMultisite() ? 'get_site_transient' : 'get_transient';
        $set_fn = $this->IsMultisite() ? 'set_site_transient' : 'set_transient';

        $data = $get_fn(self::TRANSIENT_404);
        if (!$data) {
            $data = array();
        }
        if (!isset($data[$site_id.":".$username.":".$ip])) {
            $data[$site_id.":".$username.":".$ip] = 1;
        }
        $data[$site_id.":".$username.":".$ip]++;
        $set_fn(self::TRANSIENT_404, $data, $this->Get404Expiration());
    }

    protected function IncrementVisitor404( $site_id, $username, $ip ) {
        $get_fn = $this->IsMultisite() ? 'get_site_transient' : 'get_transient';
        $set_fn = $this->IsMultisite() ? 'set_site_transient' : 'set_transient';

        $data = $get_fn( self::TRANSIENT_VISITOR_404 );
        if ( ! $data ) {
            $data = array();
        }
        if ( ! isset( $data[ $site_id . ':' . $username . ':' . $ip ] ) ) {
            $data[ $site_id . ':' . $username . ':' . $ip ] = 1;
        }
        $data[ $site_id . ':' . $username . ':' . $ip ]++;
        $set_fn( self::TRANSIENT_VISITOR_404, $data, $this->Get404Expiration() );
    }

    /**
     * Event 404 Not found.
     */
    public function Event404() {

        $attempts = 1;

        global $wp_query;
        if ( ! $wp_query->is_404 ) {
            return;
        }
        $msg = 'times';

        list( $y, $m, $d ) = explode( '-', date( 'Y-m-d' ) );

        $site_id    = ( function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0 );
        $ip         = $this->plugin->settings->GetMainClientIP();

        if ( ! is_user_logged_in() ) {
            $username = 'Website Visitor';
        } else {
            $username = wp_get_current_user()->user_login;
        }

        if ( 'Website Visitor' !== $username ) {

            // Check if the alert is disabled from the "Enable/Disable Alerts" section.
            if ( ! $this->plugin->alerts->IsEnabled( 6007 ) ) {
                return;
            }

            if ( $this->IsPast404Limit( $site_id, $username, $ip ) ) {
                return;
            }

            $objOcc = new WSAL_Models_Occurrence();

            $occ = $objOcc->CheckAlert404(
                array(
                    $ip,
                    $username,
                    6007,
                    $site_id,
                    mktime(0, 0, 0, $m, $d, $y),
                    mktime(0, 0, 0, $m, $d + 1, $y) - 1
                )
            );

            $occ = count( $occ ) ? $occ[0] : null;
            if ( ! empty( $occ ) ) {
                // update existing record.
                $this->Increment404($site_id, $username, $ip);
                $new = $occ->GetMetaValue('Attempts', 0) + 1;

                if ($new > $this->Get404LogLimit()) {
                    $new = 'more than ' . $this->Get404LogLimit();
                    $msg .= ' This could possible be a scan, therefore keep an eye on the activity from this IP Address';
                }

                $linkFile = $this->WriteLog( $new, $ip, $username );

                $occ->UpdateMetaValue('Attempts', $new);
                $occ->UpdateMetaValue('Username', $username);
                $occ->UpdateMetaValue('Msg', $msg);
                if (!empty($linkFile)) {
                    $occ->UpdateMetaValue('LinkFile', $linkFile);
                }
                $occ->created_on = null;
                $occ->Save();
            } else {
                $linkFile = $this->WriteLog( 1, $ip, $username );
                // create a new record
                $fields =  array(
                    'Attempts' => 1,
                    'Username' => $username,
                    'Msg' => $msg
                );
                if (!empty($linkFile)) {
                    $fields['LinkFile'] = $linkFile;
                }
                $this->plugin->alerts->Trigger(6007, $fields);
            }
        } else {

            // Check if the alert is disabled from the "Enable/Disable Alerts" section.
            if ( ! $this->plugin->alerts->IsEnabled( 6023 ) ) {
                return;
            }

            if ( $this->IsPastVisitor404Limit( $site_id, $username, $ip ) ) {
                return;
            }

            $objOcc = new WSAL_Models_Occurrence();

            $occ = $objOcc->CheckAlert404(
                array(
                    $ip,
                    $username,
                    6023,
                    $site_id,
                    mktime(0, 0, 0, $m, $d, $y),
                    mktime(0, 0, 0, $m, $d + 1, $y) - 1
                )
            );

            $occ = count( $occ ) ? $occ[0] : null;
            if ( ! empty( $occ ) ) {
                // update existing record.
                $this->IncrementVisitor404( $site_id, $username, $ip );
                $new = $occ->GetMetaValue( 'Attempts', 0 ) + 1;

                if ( $new > $this->GetVisitor404LogLimit() ) {
                    $new = 'more than ' . $this->GetVisitor404LogLimit();
                    $msg .= ' This could possible be a scan, therefore keep an eye on the activity from this IP Address';
                }

                $linkFile = $this->WriteLog( $new, $ip, $username, false );

                $occ->UpdateMetaValue( 'Attempts', $new );
                $occ->UpdateMetaValue( 'Username', $username );
                $occ->UpdateMetaValue( 'Msg', $msg );
                if ( ! empty( $linkFile ) ) {
                    $occ->UpdateMetaValue( 'LinkFile', $linkFile );
                }
                $occ->created_on = null;
                $occ->Save();
            } else {
                $linkFile = $this->WriteLog( 1, $ip, $username, false );
                // create a new record.
                $fields = array(
                    'Attempts'  => 1,
                    'Username'  => $username,
                    'Msg'       => $msg,
                );
                if ( ! empty( $linkFile ) ) {
                    $fields['LinkFile'] = $linkFile;
                }
                $this->plugin->alerts->Trigger( 6023, $fields );
            }
        }
    }

    /**
     * Triggered when a user accesses the admin area.
     */
    public function EventAdminInit() {

        // Destroy all the session of the same user from user profile page.
        if ( isset( $_REQUEST['action'] ) && ( 'destroy-sessions' == $_REQUEST['action'] ) && isset( $_REQUEST['user_id'] ) ) {
            $this->plugin->alerts->Trigger( 1006, array(
                'TargetUserID' => $_REQUEST['user_id'],
            ) );
        }

        // Make sure user can actually modify target options.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $actype = basename($_SERVER['SCRIPT_NAME'], '.php');
        $is_option_page = $actype == 'options';
        $is_network_settings = $actype == 'settings';
        $is_permalink_page = $actype == 'options-permalink';

        if ($is_option_page && (get_option('users_can_register') xor isset($_POST['users_can_register']))) {
            $old = get_option('users_can_register') ? 'Enabled' : 'Disabled';
            $new = isset($_POST['users_can_register']) ? 'Enabled' : 'Disabled';
            if ($old != $new) {
                $this->plugin->alerts->Trigger(6001, array(
                    'OldValue' => $old,
                    'NewValue' => $new,
                    'CurrentUserID' => wp_get_current_user()->ID,
                ));
            }
        }

        if ($is_option_page && !empty($_POST['default_role'])) {
            $old = get_option('default_role');
            $new = trim($_POST['default_role']);
            if ($old != $new) {
                $this->plugin->alerts->Trigger(6002, array(
                    'OldRole' => $old,
                    'NewRole' => $new,
                    'CurrentUserID' => wp_get_current_user()->ID,
                ));
            }
        }

        if ($is_option_page && !empty($_POST['admin_email'])) {
            $old = get_option('admin_email');
            $new = trim($_POST['admin_email']);
            if ($old != $new) {
                $this->plugin->alerts->Trigger(6003, array(
                    'OldEmail' => $old,
                    'NewEmail' => $new,
                    'CurrentUserID' => wp_get_current_user()->ID,
                ));
            }
        }

        if ($is_network_settings && !empty($_POST['admin_email'])) {
            $old = get_site_option('admin_email');
            $new = trim($_POST['admin_email']);
            if ($old != $new) {
                $this->plugin->alerts->Trigger(6003, array(
                    'OldEmail' => $old,
                    'NewEmail' => $new,
                    'CurrentUserID' => wp_get_current_user()->ID,
                ));
            }
        }

        if ($is_permalink_page && !empty($_REQUEST['permalink_structure'])) {
            $old = get_option('permalink_structure');
            $new = trim($_REQUEST['permalink_structure']);
            if ($old != $new) {
                $this->plugin->alerts->Trigger(6005, array(
                    'OldPattern' => $old,
                    'NewPattern' => $new,
                    'CurrentUserID' => wp_get_current_user()->ID,
                ));
            }
        }

        if ($action == 'do-core-upgrade' && isset($_REQUEST['version'])) {
            $oldVersion = get_bloginfo('version');
            $newVersion = $_REQUEST['version'];
            if ($oldVersion != $newVersion) {
                $this->plugin->alerts->Trigger(6004, array(
                    'OldVersion' => $oldVersion,
                    'NewVersion' => $newVersion,
                ));
            }
        }

        /* BBPress Forum support  Setting */
        if ($action == 'update' && isset($_REQUEST['_bbp_default_role'])) {
            $oldRole = get_option('_bbp_default_role');
            $newRole = $_REQUEST['_bbp_default_role'];
            if ($oldRole != $newRole) {
                $this->plugin->alerts->Trigger(8009, array(
                    'OldRole' => $oldRole,
                    'NewRole' => $newRole
                ));
            }
        }

        if ($action == 'update' && isset($_REQUEST['option_page']) && ($_REQUEST['option_page'] == 'bbpress')) {
            // Anonymous posting
            $allow_anonymous = get_option('_bbp_allow_anonymous');
            $oldStatus = !empty($allow_anonymous) ? 1 : 0;
            $newStatus = !empty($_REQUEST['_bbp_allow_anonymous']) ? 1 : 0;
            if ($oldStatus != $newStatus) {
                $status = ($newStatus == 1) ? 'Enabled' : 'Disabled';
                $this->plugin->alerts->Trigger(8010, array(
                    'Status' => $status
                ));
            }
            // Disallow editing after
            $bbp_edit_lock = get_option('_bbp_edit_lock');
            $oldTime = !empty($bbp_edit_lock) ? $bbp_edit_lock : '';
            $newTime = !empty($_REQUEST['_bbp_edit_lock']) ? $_REQUEST['_bbp_edit_lock'] : '';
            if ($oldTime != $newTime) {
                $this->plugin->alerts->Trigger(8012, array(
                    'OldTime' => $oldTime,
                    'NewTime' => $newTime
                ));
            }
            // Throttle posting every
            $bbp_throttle_time = get_option('_bbp_throttle_time');
            $oldTime2 = !empty($bbp_throttle_time) ? $bbp_throttle_time : '';
            $newTime2 = !empty($_REQUEST['_bbp_throttle_time']) ? $_REQUEST['_bbp_throttle_time'] : '';
            if ($oldTime2 != $newTime2) {
                $this->plugin->alerts->Trigger(8013, array(
                    'OldTime' => $oldTime2,
                    'NewTime' => $newTime2
                ));
            }
        }
    }

    /**
     * WordPress auto core update
     */
    public function WPUpdate($automatic)
    {
        if (isset($automatic['core'][0])) {
            $obj = $automatic['core'][0];
            $oldVersion = get_bloginfo('version');
            $this->plugin->alerts->Trigger(6004, array(
                'OldVersion' => $oldVersion,
                'NewVersion' => $obj->item->version.' (auto update)'
            ));
        }
    }

    /**
     * Purge log files older than one month.
     */
    public function LogFilesPruning() {
        if ($this->plugin->GetGlobalOption('purge-404-log', 'off') == 'on') {
            $upload_dir = wp_upload_dir();
            $uploadsDirPath = trailingslashit($upload_dir['basedir']).'wp-security-audit-log/404s/users/';
            if (is_dir($uploadsDirPath)) {
                if ($handle = opendir($uploadsDirPath)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                            if (file_exists($uploadsDirPath . $entry)) {
                                $modified = filemtime($uploadsDirPath . $entry);
                                if ($modified < strtotime('-4 weeks')) {
                                    // Delete file
                                    unlink($uploadsDirPath . $entry);
                                }
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
        if ( 'on' == $this->plugin->GetGlobalOption( 'purge-visitor-404-log', 'off' ) ) {
            $upload_dir = wp_upload_dir();
            $uploadsDirPath = trailingslashit( $upload_dir['basedir'] ) . 'wp-security-audit-log/404s/visitors/';
            if ( is_dir( $uploadsDirPath ) ) {
                if ( $handle = opendir( $uploadsDirPath ) ) {
                    while ( false !== ( $entry = readdir( $handle ) ) ) {
                        if ( $entry != "." && $entry != ".." ) {
                            if ( file_exists( $uploadsDirPath . $entry ) ) {
                                $modified = filemtime( $uploadsDirPath . $entry );
                                if ( $modified < strtotime( '-4 weeks' ) ) {
                                    // Delete file.
                                    unlink( $uploadsDirPath . $entry );
                                }
                            }
                        }
                    }
                    closedir( $handle );
                }
            }
        }
    }

    /**
     * Events from 6008 to 6018
     */
    public function EventOptions($whitelist = null)
    {
        if (isset($_REQUEST['option_page']) && $_REQUEST['option_page'] == "reading") {
            $old_status = get_option('blog_public', 1);
            $new_status = isset($_REQUEST['blog_public']) ? 0 : 1;
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6008, array(
                    'Status' => ($new_status == 0) ? 'Enabled' : 'Disabled'
                ));
            }
        }
        if (isset($_REQUEST['option_page']) && $_REQUEST['option_page'] == "discussion") {
            $old_status = get_option('default_comment_status', 'closed');
            $new_status = isset($_REQUEST['default_comment_status']) ? 'open' : 'closed';
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6009, array(
                    'Status' => ($new_status == 'open') ? 'Enabled' : 'Disabled'
                ));
            }
            $old_status = get_option('require_name_email', 0);
            $new_status = isset($_REQUEST['require_name_email']) ? 1 : 0;
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6010, array(
                    'Status' => ($new_status == 1) ? 'Enabled' : 'Disabled'
                ));
            }
            $old_status = get_option('comment_registration', 0);
            $new_status = isset($_REQUEST['comment_registration']) ? 1 : 0;
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6011, array(
                    'Status' => ($new_status == 1) ? 'Enabled' : 'Disabled'
                ));
            }
            $old_status = get_option('close_comments_for_old_posts', 0);
            $new_status = isset($_REQUEST['close_comments_for_old_posts']) ? 1 : 0;
            if ($old_status != $new_status) {
                $value = isset($_REQUEST['close_comments_days_old']) ? $_REQUEST['close_comments_days_old'] : 0;
                $this->plugin->alerts->Trigger(6012, array(
                    'Status' => ($new_status == 1) ? 'Enabled' : 'Disabled',
                    'Value' => $value
                ));
            }
            $old_value = get_option('close_comments_days_old', 0);
            $new_value = isset($_REQUEST['close_comments_days_old']) ? $_REQUEST['close_comments_days_old'] : 0;
            if ($old_value != $new_value) {
                $this->plugin->alerts->Trigger(6013, array(
                    'OldValue' => $old_value,
                    'NewValue' => $new_value
                ));
            }
            $old_status = get_option('comment_moderation', 0);
            $new_status = isset($_REQUEST['comment_moderation']) ? 1 : 0;
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6014, array(
                    'Status' => ($new_status == 1) ? 'Enabled' : 'Disabled'
                ));
            }
            $old_status = get_option('comment_whitelist', 0);
            $new_status = isset($_REQUEST['comment_whitelist']) ? 1 : 0;
            if ($old_status != $new_status) {
                $this->plugin->alerts->Trigger(6015, array(
                    'Status' => ($new_status == 1) ? 'Enabled' : 'Disabled'
                ));
            }
            $old_value = get_option('comment_max_links', 0);
            $new_value = isset($_REQUEST['comment_max_links']) ? $_REQUEST['comment_max_links'] : 0;
            if ($old_value != $new_value) {
                $this->plugin->alerts->Trigger(6016, array(
                    'OldValue' => $old_value,
                    'NewValue' => $new_value
                ));
            }
            $old_value = get_option('moderation_keys', 0);
            $new_value = isset($_REQUEST['moderation_keys']) ? $_REQUEST['moderation_keys'] : 0;
            if ($old_value != $new_value) {
                $this->plugin->alerts->Trigger(6017, array());
            }
            $old_value = get_option('blacklist_keys', 0);
            $new_value = isset($_REQUEST['blacklist_keys']) ? $_REQUEST['blacklist_keys'] : 0;
            if ($old_value != $new_value) {
                $this->plugin->alerts->Trigger(6018, array());
            }
        }
        return $whitelist;
    }

    /**
     * Write a new line on 404 log file
     * Folder: /uploads/wp-security-audit-log/404s/
     */
    private function WriteLog( $attempts, $ip, $username = '', $logged_in = true ) {
        $nameFile = null;

        if ( $logged_in ) {
            if ( 'on' == $this->plugin->GetGlobalOption( 'log-404', 'off' ) ) {
                // Request URL.
                $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                // Get option to log referrer.
                $log_referrer = $this->plugin->GetGlobalOption( 'log-404-referrer' );

                if ( 'on' === $log_referrer ) {
                    // Get the referer.
                    $referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : false;
                    // Create/Append to the log file.
                    $data = 'Request URL ' . $url . ' Referer ' . $referrer . ',';
                } else {
                    // Create/Append to the log file.
                    $data = 'Request URL ' . $url . ',';
                }

                if ( ! is_user_logged_in() ) {
                    $username = '';
                } else {
                    $username = $username . '_';
                }

                if ( '127.0.0.1' == $ip || '::1' == $ip ) {
                    $ip = 'localhost';
                }
                $upload_dir = wp_upload_dir();
                $uploadsDirPath = trailingslashit( $upload_dir['basedir'] ) . 'wp-security-audit-log/404s/users/';
                $uploadsURL = trailingslashit( $upload_dir['baseurl'] ) . 'wp-security-audit-log/404s/users/';

                // Check directory.
                if ( $this->CheckDirectory( $uploadsDirPath ) ) {
                    $filename = '6007_' . date( 'Ymd' ) . '.log';
                    $fp = $uploadsDirPath . $filename;
                    $nameFile = $uploadsURL . $filename;
                    if ( ! $file = fopen( $fp, 'a' ) ) {
                        $i = 1;
                        $fileOpened = false;
                        do {
                            $fp2 = substr( $fp, 0, -4 ) . '_' . $i . '.log';
                            if ( ! file_exists( $fp2 ) ) {
                                if ( $file = fopen( $fp2, 'a' ) ) {
                                    $fileOpened = true;
                                    $nameFile = $uploadsURL . substr( $nameFile, 0, -4 ) . '_' . $i . '.log';
                                }
                            } else {
                                $latestFilename = $this->GetLastModified( $uploadsDirPath, $filename );
                                $fpLast = $uploadsDirPath . $latestFilename;
                                if ( $file = fopen( $fpLast, 'a' ) ) {
                                    $fileOpened = true;
                                    $nameFile = $uploadsURL . $latestFilename;
                                }
                            }
                            $i++;
                        } while ( ! $fileOpened );
                    }
                    fwrite( $file, sprintf( "%s\n", $data ) );
                    fclose( $file );
                }
            }
        } else {
            if ( 'on' == $this->plugin->GetGlobalOption( 'log-visitor-404', 'off' ) ) {
                // Request URL.
                $url = $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];

                // Get option to log referrer.
                $log_referrer = $this->plugin->GetGlobalOption( 'log-visitor-404-referrer' );

                if ( 'on' === $log_referrer ) {
                    // Get the referer.
                    $referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : false;
                    // Create/Append to the log file.
                    $data = 'Request URL ' . $url . ' Referer ' . $referrer . ',';
                } else {
                    // Create/Append to the log file.
                    $data = 'Request URL ' . $url . ',';
                }

                $username = '';

                if ( '127.0.0.1' == $ip || '::1' == $ip ) {
                    $ip = 'localhost';
                }
                $upload_dir     = wp_upload_dir();
                $uploadsDirPath = trailingslashit( $upload_dir['basedir'] ) . 'wp-security-audit-log/404s/visitors/';
                $uploadsURL     = trailingslashit( $upload_dir['baseurl'] ) . 'wp-security-audit-log/404s/visitors/';

                // Check directory.
                if ( $this->CheckDirectory( $uploadsDirPath ) ) {
                    $filename = '6023_' . date( 'Ymd' ) . '.log';
                    $fp = $uploadsDirPath . $filename;
                    $nameFile = $uploadsURL . $filename;
                    if ( ! $file = fopen( $fp, 'a' ) ) {
                        $i = 1;
                        $fileOpened = false;
                        do {
                            $fp2 = substr( $fp, 0, -4 ) . '_' . $i . '.log';
                            if ( ! file_exists( $fp2 ) ) {
                                if ( $file = fopen( $fp2, 'a' ) ) {
                                    $fileOpened = true;
                                    $nameFile = $uploadsURL . substr( $nameFile, 0, -4 ) . '_' . $i . '.log';
                                }
                            } else {
                                $latestFilename = $this->GetLastModified( $uploadsDirPath, $filename );
                                $fpLast = $uploadsDirPath . $latestFilename;
                                if ( $file = fopen( $fpLast, 'a' ) ) {
                                    $fileOpened = true;
                                    $nameFile = $uploadsURL . $latestFilename;
                                }
                            }
                            $i++;
                        } while ( ! $fileOpened );
                    }
                    fwrite( $file, sprintf( "%s\n", $data ) );
                    fclose( $file );
                }
            }
        }

        return $nameFile;
    }

    /**
     * Get the latest file modified.
     * @return string $latest_filename file name
     */
    private function GetLastModified($uploadsDirPath, $filename)
    {
        $filename = substr($filename, 0, -4);
        $latest_mtime = 0;
        $latest_filename = '';
        if ($handle = opendir($uploadsDirPath)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (preg_match('/^'.$filename.'/i', $entry) > 0) {
                        if (filemtime($uploadsDirPath . $entry) > $latest_mtime) {
                            $latest_mtime = filemtime($uploadsDirPath . $entry);
                            $latest_filename = $entry;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $latest_filename;
    }
}
