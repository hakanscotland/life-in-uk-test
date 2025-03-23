<?php
/**
 * Class responsible for plugin deactivation
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 *
 * @package    Life_In_UK_Test
 * @subpackage Life_In_UK_Test/includes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class responsible for plugin deactivation
 */
class LIUK_Test_Deactivator {
    
    /**
     * Plugin deactivation function
     */
    public static function deactivate() {
        // Remove capabilities from admin role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('manage_liuk_test');
        }
        
        // Note: We're not deleting database tables on deactivation
        // This allows users to reactivate the plugin without losing data
    }
}