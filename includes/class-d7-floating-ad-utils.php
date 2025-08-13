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
     * 檢查是否應該顯示廣告
     * 
     * @return bool 是否應該顯示
     */
    public static function should_display_ad() {
        // 檢查是否為行動裝置
        if (!wp_is_mobile()) {
            return false;
        }
        
        // 檢查是否已關閉
        if (isset($_COOKIE['d7news_floating_ad_closed'])) {
            return false;
        }
        
        return true;
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
