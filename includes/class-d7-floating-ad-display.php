<?php
/**
 * 前端顯示類別
 * 
 * @package D7_Floating_Ad
 * @since 1.1.0
 */

// 避免直接存取
if (!defined('ABSPATH')) {
    exit;
}

class D7_Floating_Ad_Display {
    
    /**
     * 設定選項名稱
     */
    private $option_name;
    
    /**
     * 建構函數
     */
    public function __construct($option_name) {
        $this->option_name = $option_name;
    }
    
    /**
     * 渲染廣告
     */
    public function render() {
        $options = get_option($this->option_name);
        
        // 檢查是否啟用
        $enabled = isset($options['enabled']) ? $options['enabled'] : true;
        if (!$enabled) {
            return;
        }
        
        // 取得允許顯示的設備類型
        $display_devices = isset($options['display_devices']) && is_array($options['display_devices']) 
            ? $options['display_devices'] 
            : array('mobile');
        
        // 檢查是否應該顯示廣告（根據設備類型）
        if (!D7_Floating_Ad_Utils::should_display_ad($display_devices)) {
            return;
        }
        
        // 取得設定值
        $ad_type = isset($options['ad_type']) ? $options['ad_type'] : 'image';
        $link_url = isset($options['link_url']) ? esc_url($options['link_url']) : '#';
        $ad_width = isset($options['ad_width']) ? intval($options['ad_width']) : 150;
        $ad_height = isset($options['ad_height']) ? intval($options['ad_height']) : 150;
        $position_x = isset($options['position_x']) ? D7_Floating_Ad_Utils::validate_css_position($options['position_x']) : 'right: 15px;';
        $position_y = isset($options['position_y']) ? D7_Floating_Ad_Utils::validate_css_position($options['position_y']) : 'bottom: 15px;';
        $content_url = isset($options['image_url']) ? esc_url($options['image_url']) : '';

        // 生成內容
        $content = $this->generate_content($ad_type, $content_url, $link_url, $ad_width, $ad_height, $options);

        if (!empty($content)) {
            $this->render_ad_container($content, $position_x, $position_y);
            $this->render_javascript();
        }
    }
    
    /**
     * 生成廣告內容
     */
    private function generate_content($ad_type, $content_url, $link_url, $ad_width, $ad_height, $options) {
        $content = '';
        
        if (!empty($content_url)) {
            switch ($ad_type) {
                case 'image':
                case 'dynamic_svg':
                    $content = $this->generate_image_content($content_url, $link_url, $ad_width, $ad_height);
                    break;
                    
                case 'lottie':
                    $content = $this->generate_lottie_content($content_url, $link_url, $ad_width, $ad_height);
                    break;
            }
        } elseif ($ad_type === 'html' && !empty($options['html_content'])) {
            $content = $this->generate_html_content($options['html_content'], $link_url, $ad_width, $ad_height);
        }
        
        return $content;
    }
    
    /**
     * 生成圖片內容
     */
    private function generate_image_content($image_url, $link_url, $width, $height) {
        return sprintf(
            '<a href="%s" rel="ugc nofollow sponsored"><img src="%s" alt="浮動廣告" style="width: %dpx; height: %dpx;" /></a>',
            $link_url,
            $image_url,
            $width,
            $height
        );
    }
    
    /**
     * 生成 Lottie 內容
     */
    private function generate_lottie_content($json_url, $link_url, $width, $height) {
        return sprintf(
            '<a href="%s" rel="ugc nofollow sponsored" style="display:block; width: %dpx; height: %dpx;">
                <div class="lottie-animation" data-path="%s" style="width:100%%; height:100%%;"></div>
            </a>',
            $link_url,
            $width,
            $height,
            $json_url
        );
    }
    
    /**
     * 生成 HTML 內容
     */
    private function generate_html_content($html_content, $link_url, $width, $height) {
        $sanitized_html = wp_kses_post($html_content);
        return sprintf(
            '<a href="%s" rel="ugc nofollow sponsored" style="display: block; width: %dpx; height: %dpx;">%s</a>',
            $link_url,
            $width,
            $height,
            $sanitized_html
        );
    }
    
    /**
     * 渲染廣告容器
     */
    private function render_ad_container($content, $position_x, $position_y) {
        ?>
        <div id="d7news-floating-ad-container" style="
            position: fixed;
            z-index: 9999;
            <?php echo $position_x; ?>
            <?php echo $position_y; ?>
            ">
            <button id="d7news-floating-ad-close-btn" style="
                position: absolute;
                top: -10px;
                right: -10px;
                background: #000;
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                text-align: center;
                box-shadow: 0 0 5px rgba(0,0,0,0.5);
                ">×</button>
            <div id="d7news-floating-ad-content">
                <?php echo $content; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染 JavaScript
     */
    private function render_javascript() {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const closeBtn = document.getElementById('d7news-floating-ad-close-btn');
                const adContainer = document.getElementById('d7news-floating-ad-container');
                
                // 關閉按鈕功能
                if (closeBtn && adContainer) {
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        adContainer.style.display = 'none';
                        
                        // 設定 Cookie，24 小時後過期
                        const expires = new Date(Date.now() + 86400000).toUTCString();
                        document.cookie = "d7news_floating_ad_closed=true; expires=" + expires + "; path=/; SameSite=Lax";
                    });
                }
                
                // Lottie 動畫初始化
                this.initLottieAnimations();
            });
            
            // Lottie 動畫初始化函數
            function initLottieAnimations() {
                const lottieContainers = document.querySelectorAll('.lottie-animation');
                if (typeof lottie !== 'undefined' && lottieContainers.length > 0) {
                    lottieContainers.forEach(container => {
                        const animationPath = container.getAttribute('data-path');
                        if (animationPath) {
                            try {
                                lottie.loadAnimation({
                                    container: container,
                                    renderer: 'svg',
                                    loop: true,
                                    autoplay: true,
                                    path: animationPath
                                });
                            } catch (error) {
                                console.warn('Lottie 動畫載入失敗:', error);
                            }
                        }
                    });
                }
            }
        </script>
        <?php
    }
}
