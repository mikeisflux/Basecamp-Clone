# ProjectFOB WordPress Theme

**Every great plan deploys from the FOB.**

Official WordPress theme for the ProjectFOB SaaS platform.

## Description

ProjectFOB is a modern, responsive WordPress theme designed specifically to work with the ProjectFOB plugin. It provides a beautiful homepage, pricing page integration, and seamless access to the ProjectFOB project management platform.

## Features

- **Beautiful Homepage**: Eye-catching hero section with clear call-to-actions
- **Responsive Design**: Works perfectly on all devices
- **Plugin Integration**: Seamlessly integrates with ProjectFOB plugin
- **Customizable**: Color schemes, hero text, and more via WordPress Customizer
- **Blog Support**: Full support for WordPress blog posts
- **Widget Areas**: 3 footer widget areas + sidebar
- **Navigation Menus**: Primary header menu + footer menu
- **SEO Friendly**: Clean, semantic HTML5 markup

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- **ProjectFOB Plugin** (required for full functionality)

## Installation

### Method 1: WordPress Admin

1. Download `projectfob-theme.zip`
2. Go to WordPress Admin > Appearance > Themes
3. Click "Add New" > "Upload Theme"
4. Choose the ZIP file and click "Install Now"
5. Click "Activate"

### Method 2: FTP Upload

1. Extract the `projectfob-theme.zip` file
2. Upload the `projectfob-theme` folder to `/wp-content/themes/`
3. Go to WordPress Admin > Appearance > Themes
4. Find "ProjectFOB" and click "Activate"

## Setup

After activating the theme:

1. **Install ProjectFOB Plugin** (required)
   - Go to Plugins > Add New > Upload Plugin
   - Install and activate projectfob-2.0.0.zip

2. **Configure Menus**
   - Go to Appearance > Menus
   - Create a Primary Menu with links to:
     - Home
     - Pricing (/projectfob/pricing)
     - Blog
     - About/Contact
   - Assign to "Primary Menu" location

3. **Customize Theme**
   - Go to Appearance > Customize
   - Update colors, hero text, and logo
   - Save changes

4. **Add Footer Widgets** (optional)
   - Go to Appearance > Widgets
   - Add widgets to Footer 1, 2, 3 areas

5. **Set Homepage**
   - Go to Settings > Reading
   - Choose "A static page" if you want custom homepage
   - Or leave as "Your latest posts" to use theme homepage

## Theme Structure

```
projectfob-theme/
├── assets/
│   ├── css/
│   │   └── custom.css          # Theme custom styles
│   └── js/
│       └── main.js              # Theme JavaScript
├── inc/
│   └── customizer.php           # Theme customizer settings
├── functions.php                # Theme functions
├── header.php                   # Header template
├── footer.php                   # Footer template
├── index.php                    # Homepage template
├── page.php                     # Page template
├── single.php                   # Single post template
├── style.css                    # Main stylesheet + theme info
├── screenshot.png               # Theme screenshot
└── README.md                    # This file
```

## Customization

### Colors

Go to **Appearance > Customize > ProjectFOB Settings**:
- Primary Color: Main brand color (buttons, links)
- Secondary Color: Gradient secondary color

### Hero Section

Go to **Appearance > Customize > ProjectFOB Settings**:
- Hero Title: Main homepage headline
- Hero Subtitle: Homepage subtitle text

### Custom Logo

Go to **Appearance > Customize > Site Identity**:
- Upload your logo (recommended: 200x50px)

### CSS Variables

The theme uses CSS custom properties for easy customization:

```css
:root {
    --pfob-primary: #667eea;
    --pfob-secondary: #764ba2;
    --pfob-accent: #f093fb;
    --pfob-dark: #1a202c;
    --pfob-gray: #4a5568;
    --pfob-light: #f7fafc;
}
```

## Important URLs

When ProjectFOB plugin is active, these URLs work:

- **Homepage**: `https://your-site.com/`
- **Pricing**: `https://your-site.com/projectfob/pricing`
- **Signup**: `https://your-site.com/projectfob/signup`
- **Dashboard**: `https://your-site.com/projectfob/` (requires login)

## Troubleshooting

### Theme shows warning about plugin

**Solution**: Install and activate the ProjectFOB plugin. The theme requires the plugin for full functionality.

### Pricing/Dashboard links don't work

**Solution**:
1. Make sure ProjectFOB plugin is activated
2. Go to Settings > Permalinks and click Save Changes

### Homepage doesn't look right

**Solution**: Make sure you're viewing the front page, not a static page set in Settings > Reading

## Support

For support, please visit:
- GitHub: https://github.com/your-repo
- Documentation: https://projectfob.com/docs

## Changelog

### Version 1.0.0
- Initial release
- Homepage with hero section
- Feature grid
- Blog support
- Full responsive design
- Customizer integration
- ProjectFOB plugin integration

## License

ProjectFOB Theme is licensed under the GNU General Public License v2 or later.

## Credits

- Theme: Divinity Comics Inc
- Font Stack: System fonts for optimal performance
- Icons: Unicode emoji characters
