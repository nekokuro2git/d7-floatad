<?php
/**
 * 主要外掛類別
 * 
 * @package D7_Floating_Ad
 * @since 1.1.0
 */

// 避免直接存取
if (!defined('ABSPATH')) {
    exit;
}

class D7_Floating_Ad {
    
    /**
     * 外掛版本
     */
    const VERSION = '1.1.0';
    
    /**
     * 外掛實例
     */
    private static $instance = null;
    
    /**
     * 設定選項名稱
     */
    private $option_name = 'd7news_floating_ad_settings';
    
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
        $this->init_hooks();
    }
    
    /**
     * 初始化勾點
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('wp_footer', array($this, 'display_ad'));
    }
    
    /**
     * 初始化外掛
     */
    public function init() {
        // 載入其他檔案
        $this->load_dependencies();
        
        // 初始化管理介面
        if (is_admin()) {
            new D7_Floating_Ad_Admin($this->option_name);
        }
    }
    
    /**
     * 載入依賴檔案
     */
    private function load_dependencies() {
        $plugin_dir = plugin_dir_path(__FILE__);
        
        // 載入工具類別
        require_once $plugin_dir . 'class-d7-floating-ad-utils.php';
        
        // 載入管理介面類別
        if (is_admin()) {
            require_once $plugin_dir . 'class-d7-floating-ad-admin.php';
        }
        
        // 載入前端顯示類別
        require_once $plugin_dir . 'class-d7-floating-ad-display.php';
    }
    
    /**
     * 載入後台腳本
     */
    public function admin_scripts($hook) {
        if ($hook === 'settings_page_d7news-floating-ad') {
            wp_enqueue_media();
            wp_enqueue_script(
                'd7news-floating-ad-media-uploader',
                plugins_url('js/media-uploader.js', dirname(__FILE__)),
                array('jquery'),
                self::VERSION,
                true
            );
        }
    }
    
    /**
     * 載入前端腳本
     */
    public function frontend_scripts() {
        $options = get_option($this->option_name);
        $ad_type = isset($options['ad_type']) ? $options['ad_type'] : 'image';

        // 如果選擇的是 Lottie 動畫，則在前端載入 Lottie 播放器
        if (wp_is_mobile() && $ad_type === 'lottie') {
            wp_enqueue_script(
                'lottie-player',
                'https://cdn.jsdelivr.net/npm/lottie-web@5.11.0/build/player/lottie.min.js',
                array(),
                '5.11.0',
                true
            );
            
            // 添加錯誤處理
            wp_add_inline_script('lottie-player', '
                window.addEventListener("error", function(e) {
                    if (e.target.src && e.target.src.includes("lottie")) {
                        console.warn("Lottie 動畫載入失敗，請檢查網路連線或檔案路徑");
                    }
                });
            ');
        }
    }
    
    /**
     * 顯示浮動廣告
     */
    public function display_ad() {
        $display = new D7_Floating_Ad_Display($this->option_name);
        $display->render();
    }
    
    /**
     * 取得設定選項
     */
    public function get_option($key = null, $default = null) {
        $options = get_option($this->option_name, array());
        
        if ($key === null) {
            return $options;
        }
        
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * 啟用外掛時的處理
     */
    public static function activate() {
        // 設定預設選項
        $default_options = array(
            'enabled' => true,
            'ad_type' => 'image',
            'ad_width' => 150,
            'ad_height' => 150,
            'position_x' => 'right: 15px;',
            'position_y' => 'bottom: 15px;'
        );
        
        add_option('d7news_floating_ad_settings', $default_options);
    }
    
    /**
     * 停用外掛時的處理
     */
    public static function deactivate() {
        // 清理工作（可選）
    }
    
    /**
     * 移除外掛時的處理
     */
    public static function uninstall() {
        // 移除設定選項
        delete_option('d7news_floating_ad_settings');
    }
}
