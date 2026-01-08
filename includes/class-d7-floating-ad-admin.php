<?php
/**
 * 管理介面類別
 * 
 * @package D7_Floating_Ad
 * @since 1.1.0
 */

// 避免直接存取
if (!defined('ABSPATH')) {
    exit;
}

class D7_Floating_Ad_Admin {
    
    /**
     * 設定選項名稱
     */
    private $option_name;
    
    /**
     * 建構函數
     */
    public function __construct($option_name) {
        $this->option_name = $option_name;
        $this->init_hooks();
    }
    
    /**
     * 初始化勾點
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    /**
     * 添加管理選單
     */
    public function add_admin_menu() {
        add_options_page(
            '浮動廣告設定',
            '浮動廣告',
            'manage_options',
            'd7news-floating-ad',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * 初始化設定
     */
    public function init_settings() {
        register_setting($this->option_name . '_options', $this->option_name, array(
            'sanitize_callback' => array('D7_Floating_Ad_Utils', 'sanitize_settings')
        ));

        add_settings_section(
            'd7news_floating_ad_section',
            '浮動廣告設定',
            array($this, 'render_section_description'),
            'd7news-floating-ad'
        );

        // 添加設定欄位
        $this->add_settings_fields();
    }
    
    /**
     * 添加設定欄位
     */
    private function add_settings_fields() {
        $fields = array(
            'enabled' => array(
                'title' => '啟用浮動廣告',
                'callback' => 'render_enabled_field'
            ),
            'display_devices' => array(
                'title' => '顯示設備',
                'callback' => 'render_display_devices_field'
            ),
            'ad_type' => array(
                'title' => '內容類型',
                'callback' => 'render_ad_type_field'
            ),
            'image_url' => array(
                'title' => '內容網址',
                'callback' => 'render_image_url_field'
            ),
            'html_content' => array(
                'title' => 'HTML 內容',
                'callback' => 'render_html_content_field'
            ),
            'link_url' => array(
                'title' => '連結網址',
                'callback' => 'render_link_url_field'
            ),
            'ad_width' => array(
                'title' => '顯示寬度 (px)',
                'callback' => 'render_ad_width_field'
            ),
            'ad_height' => array(
                'title' => '顯示高度 (px)',
                'callback' => 'render_ad_height_field'
            ),
            'position_x' => array(
                'title' => '水平位置 (X 軸)',
                'callback' => 'render_position_x_field'
            ),
            'position_y' => array(
                'title' => '垂直位置 (Y 軸)',
                'callback' => 'render_position_y_field'
            )
        );

        foreach ($fields as $field_id => $field) {
            add_settings_field(
                $field_id,
                $field['title'],
                array($this, $field['callback']),
                'd7news-floating-ad',
                'd7news_floating_ad_section'
            );
        }
    }
    
    /**
     * 渲染區段描述
     */
    public function render_section_description() {
        echo '<p>設定浮動廣告的顯示內容、位置和樣式。可以選擇在行動裝置、平板或桌機上顯示廣告。</p>';
    }
    
    /**
     * 渲染啟用欄位
     */
    public function render_enabled_field() {
        $options = get_option($this->option_name);
        $enabled = isset($options['enabled']) ? $options['enabled'] : true;
        ?>
        <label>
            <input type="checkbox" name="<?php echo $this->option_name; ?>[enabled]" value="1" <?php checked($enabled, true); ?> />
            啟用浮動廣告功能
        </label>
        <p class="description">取消勾選可暫時停用浮動廣告，而不需要刪除設定。</p>
        <?php
    }
    
    /**
     * 渲染顯示設備欄位
     */
    public function render_display_devices_field() {
        $options = get_option($this->option_name);
        $display_devices = isset($options['display_devices']) && is_array($options['display_devices']) 
            ? $options['display_devices'] 
            : array('mobile');
        
        $devices = array(
            'mobile' => '行動裝置（手機）',
            'tablet' => '平板裝置',
            'desktop' => '桌機'
        );
        ?>
        <fieldset>
            <?php foreach ($devices as $device_key => $device_label): ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" 
                           name="<?php echo $this->option_name; ?>[display_devices][]" 
                           value="<?php echo esc_attr($device_key); ?>" 
                           <?php checked(in_array($device_key, $display_devices), true); ?> />
                    <?php echo esc_html($device_label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <p class="description">選擇要在哪些設備上顯示浮動廣告。可以同時選擇多個設備類型。</p>
        <?php
    }
    
    /**
     * 渲染廣告類型欄位
     */
    public function render_ad_type_field() {
        $options = get_option($this->option_name);
        $ad_type = isset($options['ad_type']) ? $options['ad_type'] : 'image';
        ?>
        <select name="<?php echo $this->option_name; ?>[ad_type]" id="d7news_ad_type">
            <option value="image" <?php selected($ad_type, 'image'); ?>>圖片 (JPG, PNG, GIF)</option>
            <option value="dynamic_svg" <?php selected($ad_type, 'dynamic_svg'); ?>>動態 SVG</option>
            <option value="lottie" <?php selected($ad_type, 'lottie'); ?>>Lottie JSON</option>
            <option value="html" <?php selected($ad_type, 'html'); ?>>文字 (HTML)</option>
        </select>
        <?php
    }
    
    /**
     * 渲染圖片 URL 欄位
     */
    public function render_image_url_field() {
        $options = get_option($this->option_name);
        $image_url = isset($options['image_url']) ? esc_url($options['image_url']) : '';
        ?>
        <input type="url" name="<?php echo $this->option_name; ?>[image_url]" id="d7news-image-url" value="<?php echo $image_url; ?>" class="regular-text" />
        <button type="button" class="button d7news-media-upload">選擇檔案</button>
        <p class="description">請輸入圖片、SVG 或 Lottie JSON 的完整網址，或點擊按鈕從媒體庫選擇。</p>
        <?php
    }
    
    /**
     * 渲染 HTML 內容欄位
     */
    public function render_html_content_field() {
        $options = get_option($this->option_name);
        $html_content = isset($options['html_content']) ? esc_textarea($options['html_content']) : '';
        ?>
        <textarea name="<?php echo $this->option_name; ?>[html_content]" class="large-text" rows="5"><?php echo $html_content; ?></textarea>
        <p class="description">請輸入您的 HTML 內容。**請勿包含 `<a>` 標籤，連結將由下方設定產生。**</p>
        <?php
    }
    
    /**
     * 渲染連結 URL 欄位
     */
    public function render_link_url_field() {
        $options = get_option($this->option_name);
        $link_url = isset($options['link_url']) ? esc_url($options['link_url']) : '';
        ?>
        <input type="url" name="<?php echo $this->option_name; ?>[link_url]" value="<?php echo $link_url; ?>" class="regular-text" />
        <p class="description">點擊廣告後要前往的網址。</p>
        <?php
    }
    
    /**
     * 渲染寬度欄位
     */
    public function render_ad_width_field() {
        $options = get_option($this->option_name);
        $ad_width = isset($options['ad_width']) ? intval($options['ad_width']) : 150;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[ad_width]" value="<?php echo $ad_width; ?>" min="1" max="1000" /> px
        <?php
    }
    
    /**
     * 渲染高度欄位
     */
    public function render_ad_height_field() {
        $options = get_option($this->option_name);
        $ad_height = isset($options['ad_height']) ? intval($options['ad_height']) : 150;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[ad_height]" value="<?php echo $ad_height; ?>" min="1" max="1000" /> px
        <?php
    }
    
    /**
     * 渲染水平位置欄位
     */
    public function render_position_x_field() {
        $options = get_option($this->option_name);
        $position_x = isset($options['position_x']) ? esc_attr($options['position_x']) : 'right: 15px;';
        ?>
        <input type="text" name="<?php echo $this->option_name; ?>[position_x]" value="<?php echo $position_x; ?>" class="regular-text" />
        <p class="description">請輸入 CSS 定位屬性，例如：`right: 15px;` 或 `left: 15px;`。</p>
        <?php
    }
    
    /**
     * 渲染垂直位置欄位
     */
    public function render_position_y_field() {
        $options = get_option($this->option_name);
        $position_y = isset($options['position_y']) ? esc_attr($options['position_y']) : 'bottom: 15px;';
        ?>
        <input type="text" name="<?php echo $this->option_name; ?>[position_y]" value="<?php echo $position_y; ?>" class="regular-text" />
        <p class="description">請輸入 CSS 定位屬性，例如：`bottom: 15px;` 或 `top: 15px;`。</p>
        <?php
    }
    
    /**
     * 渲染設定頁面
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2>浮動廣告設定</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->option_name . '_options');
                do_settings_sections('d7news-floating-ad');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
