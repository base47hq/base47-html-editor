# B47 Hero Video Widget

A bold portfolio hero section with image, animated category text, and oversized brand typography.

## Usage

### Basic Usage
```
[base47_b47_hero_video]
```

### Advanced Usage with Custom Options
```
[base47_b47_hero_video 
    image_url="https://your-domain.com/hero-image.jpg"
    small_title="Your bold visual"
    main_title="story that captivates audiences."
    big_text="Brand"
    categories="Design,Web,Mobile,Branding,Creative,Studio"
    bg_color="#f0f0f0"
    text_color="#333333"
    accent_color="#999999"
]
```

### Legacy Widget System Usage
```
[base47_widget slug="b47-hero-video"]
```

## Parameters

- **image_url**: URL to the hero image (default: placeholder image)
- **small_title**: The smaller title text (default: "Creating bold visual")
- **main_title**: The main title text (default: "narratives that inspire and engage.")
- **big_text**: The large background text (default: "B47")
- **categories**: Comma-separated list of category words for animation (default: "Design,Branding,Development,Portfolio,Agency,Portfolio")
- **bg_color**: Background color for category section (default: "#f8f8f9")
- **text_color**: Main text color (default: "#000000")
- **accent_color**: Accent text color (default: "#666666")

## Features

- ✅ Fully responsive design
- ✅ Optimized image loading with lazy loading
- ✅ Animated category marquee
- ✅ Scoped CSS (no conflicts)
- ✅ Multiple instances support
- ✅ RTL language support
- ✅ Performance optimized
- ✅ Accessibility compliant
- ✅ WordPress coding standards
- ✅ Image fallback for failed loads

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- All modern mobile browsers

## File Structure

```
b47-hero-video/
├── widget.json                    # Widget manifest
├── b47-hero-video.html            # Static HTML (legacy system)
├── render.php                     # PHP render template (new system)
├── assets/
│   ├── css/
│   │   └── b47-hero-video.css     # Scoped CSS
│   └── js/
│       └── b47-hero-video.js      # Scoped JavaScript
├── preview.png                    # Widget preview image
└── README.md                      # This file
```

## Technical Notes

- All CSS is scoped under `.base47-widget-b47-hero-video`
- JavaScript uses no global variables
- Images support lazy loading for performance
- Custom CSS variables for theming
- No external dependencies
- Automatic fallback for failed image loads

## Pro Features

This widget supports both Free and Pro versions:
- **Free**: Basic widget functionality
- **Pro**: Unlimited instances, advanced customization

## License

Part of Base47 HTML Editor plugin. See main plugin license.