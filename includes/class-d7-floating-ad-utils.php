<?php
/**
 * 工具類別
 * 
 * @package D7_Floating_Ad
 * @since 1.1.0
 */

// 避免直接存取
if (!defined('ABSPATH')) {
    exit;
}

class D7_Floating_Ad_Utils {
    
    /**
     * CSS 屬性驗證函數
     * 
     * @param string $value CSS 定位值
     * @return string 驗證後的 CSS 值
     */
    public static function validate_css_position($value) {
        // 只允許安全的 CSS 定位屬性
        $allowed_properties = array('left', 'right', 'top', 'bottom');
        $pattern = '/^(' . implode('|', $allowed_properties) . '):\s*\d+px;?$/';
        
        if (preg_match($pattern, trim($value))) {
            return esc_attr($value);
        }
        
        // 返回預設值
        return 'right: 15px;';
    }
    
    /**
     * 驗證圖片 URL
     * 
     * @param string $url 圖片 URL
     * @return bool 是否為有效的圖片 URL
     */
    public static function is_valid_image_url($url) {
        if (empty($url)) {
            return false;
        }
        
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
        $file_extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        
        return in_array($file_extension, $allowed_extensions);
    }
    
    /**
     * 驗證 Lottie JSON URL
     * 
     * @param string $url Lottie JSON URL
     * @return bool 是否為有效的 JSON URL
     */
    public static function is_valid_lottie_url($url) {
        if (empty($url)) {
            return false;
        }
        
        $file_extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        return $file_extension === 'json';
    }
    
    /**
     * 取得預設設定
     * 
     * @return array 預設設定陣列
     */
    public static function get_default_settings() {
        return array(
            'enabled' => true,
            'display_devices' => array('mobile'), // 預設只顯示在行動裝置
            'ad_type' => 'image',
            'image_url' => '',
            'html_content' => '',
            'link_url' => '',
            'ad_width' => 150,
            'ad_height' => 150,
            'position_x' => 'right: 15px;',
            'position_y' => 'bottom: 15px;'
        );
    }
    
    /**
     * 清理設定資料
     * 
     * @param array $settings 原始設定
     * @return array 清理後的設定
     */
    public static function sanitize_settings($settings) {
        $sanitized = array();
        
        // 啟用狀態
        $sanitized['enabled'] = isset($settings['enabled']) ? (bool) $settings['enabled'] : true;
        
        // 顯示設備（多選）
        $allowed_devices = array('mobile', 'tablet', 'desktop');
        $sanitized['display_devices'] = array();
        if (isset($settings['display_devices']) && is_array($settings['display_devices'])) {
            foreach ($settings['display_devices'] as $device) {
                if (in_array($device, $allowed_devices)) {
                    $sanitized['display_devices'][] = $device;
                }
            }
        }
        // 如果沒有選擇任何設備，預設為行動裝置
        if (empty($sanitized['display_devices'])) {
            $sanitized['display_devices'] = array('mobile');
        }
        
        // 廣告類型
        $allowed_types = array('image', 'dynamic_svg', 'lottie', 'html');
        $sanitized['ad_type'] = isset($settings['ad_type']) && in_array($settings['ad_type'], $allowed_types) 
            ? $settings['ad_type'] : 'image';
        
        // 圖片 URL
        $sanitized['image_url'] = isset($settings['image_url']) ? esc_url_raw($settings['image_url']) : '';
        
        // HTML 內容
        $sanitized['html_content'] = isset($settings['html_content']) ? wp_kses_post($settings['html_content']) : '';
        
        // 連結 URL
        $sanitized['link_url'] = isset($settings['link_url']) ? esc_url_raw($settings['link_url']) : '';
        
        // 寬度
        $sanitized['ad_width'] = isset($settings['ad_width']) ? absint($settings['ad_width']) : 150;
        $sanitized['ad_width'] = max(1, min(1000, $sanitized['ad_width'])); // 限制範圍 1-1000
        
        // 高度
        $sanitized['ad_height'] = isset($settings['ad_height']) ? absint($settings['ad_height']) : 150;
        $sanitized['ad_height'] = max(1, min(1000, $sanitized['ad_height'])); // 限制範圍 1-1000
        
        // 位置
        $sanitized['position_x'] = isset($settings['position_x']) ? self::validate_css_position($settings['position_x']) : 'right: 15px;';
        $sanitized['position_y'] = isset($settings['position_y']) ? self::validate_css_position($settings['position_y']) : 'bottom: 15px;';
        
        return $sanitized;
    }
    
    /**
     * 檢測當前設備類型
     * 
     * @return string 設備類型：mobile, tablet, desktop
     */
    public static function detect_device_type() {
        // 檢查 User Agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        
        // 檢測平板裝置
        $tablet_patterns = array('ipad', 'tablet', 'playbook', 'kindle', 'silk', 'gt-p', 'gt-n', 'sgh-t', 'nexus 7', 'nexus 10', 'touchpad', 'xoom');
        foreach ($tablet_patterns as $pattern) {
            if (strpos($user_agent, $pattern) !== false) {
                return 'tablet';
            }
        }
        
        // 檢測行動裝置
        if (wp_is_mobile()) {
            return 'mobile';
        }
        
        // 其他情況視為桌機
        return 'desktop';
    }
    
    /**
     * 檢查是否應該顯示廣告
     * 
     * @param array $display_devices 允許顯示的設備類型陣列
     * @return bool 是否應該顯示
     */
    public static function should_display_ad($display_devices = array('mobile')) {
        // 檢查是否已關閉
        if (isset($_COOKIE['d7news_floating_ad_closed'])) {
            return false;
        }
        
        // 如果沒有指定設備，預設不顯示
        if (empty($display_devices) || !is_array($display_devices)) {
            return false;
        }
        
        // 檢測當前設備類型
        $current_device = self::detect_device_type();
        
        // 檢查當前設備是否在允許的設備列表中
        return in_array($current_device, $display_devices);
    }
    
    /**
     * 記錄錯誤日誌
     * 
     * @param string $message 錯誤訊息
     * @param string $level 錯誤等級
     */
    public static function log_error($message, $level = 'error') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("D7 Floating Ad [{$level}]: {$message}");
        }
    }
}
