# Dubai7 Floating Ad Plugin

A floating ad plugin designed for WordPress, supporting multiple content types and device selection.

## 🚀 Features

- **Multiple Content Types**: Supports images, dynamic SVG, Lottie animations, and HTML text
- **Device Selection**: Choose to display ads on mobile devices, tablets, or desktops
- **Dismissible Design**: Users can close ads, and they won't appear again for 24 hours
- **Flexible Positioning**: Customize horizontal and vertical ad positions
- **Security Validation**: All inputs are validated to prevent XSS attacks
- **Modular Design**: Uses object-oriented programming for easy maintenance and extension

## 📁 File Structure

```
d7-floatad/
├── d7-floatad.php              # Main file (plugin entry point)
├── includes/                   # Core class files
│   ├── class-d7-floating-ad.php           # Main plugin class
│   ├── class-d7-floating-ad-utils.php     # Utility class
│   ├── class-d7-floating-ad-admin.php     # Admin interface class
│   └── class-d7-floating-ad-display.php   # Frontend display class
├── js/
│   └── media-uploader.js       # Media uploader JavaScript
└── README.md                   # Documentation
```

## 🛠️ Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin in WordPress admin
3. Go to **Settings** → **Floating Ad** to configure

## ⚙️ Configuration

### Basic Settings
- **Enable Floating Ad**: Turn the plugin on or off
- **Display Devices**: Choose which devices to display ads on (multiple selection available)
  - Mobile devices (phones)
  - Tablets
  - Desktop
- **Content Type**: Choose the content type to display
- **Content URL**: URL of image, SVG, or Lottie JSON
- **HTML Content**: Custom HTML text content
- **Link URL**: URL to navigate to when the ad is clicked

### Display Settings
- **Display Width**: Ad width (in pixels)
- **Display Height**: Ad height (in pixels)
- **Horizontal Position**: CSS positioning property (e.g., `right: 15px;`)
- **Vertical Position**: CSS positioning property (e.g., `bottom: 15px;`)

## 🔧 Developer Information

### Class Architecture

#### D7_Floating_Ad (Main Class)
- Handles plugin initialization and coordination
- Manages script loading and hook registration
- Provides singleton pattern to ensure unique instance

#### D7_Floating_Ad_Utils (Utility Class)
- Provides validation and sanitization functions
- Handles CSS property validation
- Manages default settings and error logging

#### D7_Floating_Ad_Admin (Admin Interface Class)
- Handles backend settings page
- Manages settings field rendering
- Handles settings storage and validation

#### D7_Floating_Ad_Display (Frontend Display Class)
- Responsible for frontend ad rendering
- Handles different content type generation
- Manages JavaScript functionality

### Extending the Plugin

#### Adding New Content Types
1. Add the new type in `D7_Floating_Ad_Utils::sanitize_settings()`
2. Add the option in `D7_Floating_Ad_Admin::render_ad_type_field()`
3. Add processing logic in `D7_Floating_Ad_Display::generate_content()`

#### Adding New Settings Fields
1. Define the field in `D7_Floating_Ad_Admin::add_settings_fields()`
2. Create the corresponding rendering method
3. Add validation in `D7_Floating_Ad_Utils::sanitize_settings()`

## 🎯 AI Coding Advantages

This modular design offers the following advantages for AI programming:

### 1. **Clear Separation of Concerns**
- Each class has a clear functional scope
- AI can more easily understand the purpose of each part

### 2. **Easy Maintenance and Extension**
- When modifying specific features, only focus on the corresponding class
- When adding new features, follow existing patterns

### 3. **Better Code Reusability**
- Utility functions can be reused in multiple places
- Reduces code duplication and improves efficiency

### 4. **Simplified Main File**
- Main file is only 70 lines with clear logic
- AI can quickly understand the overall plugin structure

## 📝 Version History

### v1.3.0
- Added device selection feature: Choose to display ads on mobile devices, tablets, or desktops
- Improved device detection logic for more accurate distinction between phones, tablets, and desktops
- Updated frontend script loading logic to load Lottie and other scripts based on device selection

### v1.2.0
- Refactored to modular architecture
- Improved code organization and maintainability
- Enhanced security validation
- Added better error handling

### v1.1.0
- Added enable/disable feature
- Improved Cookie settings
- Enhanced JavaScript error handling

### v1.0.0
- Initial version
- Basic floating ad functionality

## 🤝 Contributing

Welcome to submit Issues and Pull Requests to improve this plugin.

## 📄 License

This project is licensed under the MIT License.
