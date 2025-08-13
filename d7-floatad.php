<?php
/**
 * Plugin Name:       Dubai7 浮動廣告
 * Description:       在行動裝置上顯示可開關的浮動圖片、文字、動態 SVG 或 Lottie 動畫。
 * Version:           1.2.0
 * Author:            Hedula
 * Author Website:    https://hedula.com 
 * Text Domain:       d7-floating-ad
 * Domain Path:       /languages
 */

// 避免直接存取檔案
if (!defined('ABSPATH')) {
    exit;
}

// 定義外掛常數
define('D7_FLOATING_AD_VERSION', '1.2.0');
define('D7_FLOATING_AD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('D7_FLOATING_AD_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * 主要外掛類別
 */
final class D7_Floating_Ad_Plugin {
    
    /**
     * 外掛實例
     */
    private static $instance = null;
    
    /**
     * 主要外掛類別
     */
    private $plugin;
    
    /**
     * 取得實例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 建構函數
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * 初始化外掛
     */
    private function init() {
        // 載入主要類別
        require_once D7_FLOATING_AD_PLUGIN_DIR . 'includes/class-d7-floating-ad.php';
        
        // 初始化主要外掛類別
        $this->plugin = D7_Floating_Ad::get_instance();
        
        // 註冊啟用/停用/移除勾點
        register_activation_hook(__FILE__, array('D7_Floating_Ad', 'activate'));
        register_deactivation_hook(__FILE__, array('D7_Floating_Ad', 'deactivate'));
        register_uninstall_hook(__FILE__, array('D7_Floating_Ad', 'uninstall'));
    }
    
    /**
     * 取得主要外掛類別
     */
    public function get_plugin() {
        return $this->plugin;
    }
}

// 初始化外掛
function d7_floating_ad_init() {
    return D7_Floating_Ad_Plugin::get_instance();
}

// 啟動外掛
add_action('plugins_loaded', 'd7_floating_ad_init');