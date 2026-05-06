# Base47 Soft UI Icon & Logo Guide

## Created Icons - Soft UI Design

I've created custom Base47 icons with **rounded corners** (Soft UI style) and **orange accent colors**:

### 1. **base47-icon.svg** ⭐ MAIN VERSION
- Soft UI rounded layout grid
- **Orange (#fb6340)** accents on top bar and top-right block
- **Blue (#5e72e4)** on left column and bottom-right block
- Perfect for: Plugin menu icon, favicons, app icons
- Size: 20x20px viewBox

### 2. **base47-icon-white.svg**
- White version with subtle opacity variations
- Perfect for: Dark backgrounds, dark mode, headers

### 3. **base47-icon-gradient.svg** ✨ PREMIUM
- Beautiful gradient version
- Orange-to-red and blue-to-purple gradients
- Perfect for: Marketing materials, hero sections, premium branding

### 4. **base47-icon-monochrome.svg**
- Single color version using `currentColor`
- Adapts to any color context
- Perfect for: UI elements that need to match theme colors

### 5. **base47-logo.svg**
- Full logo with Soft UI icon + "Base47" text
- Perfect for: Headers, about pages, full branding

## How to Use in Plugin

### Update the Plugin Menu Icon

Edit `inc/admin-init.php` line 29:

**Current:**
```php
'dashicons-layout',
```

**Change to:**
```php
plugins_url('admin-assets/images/base47-icon.svg', dirname(__FILE__)),
```

## Generate PNG Versions from SVG

You have several options:

### Option 1: Online Converter (Easiest)
1. Go to: https://cloudconvert.com/svg-to-png
2. Upload the SVG file
3. Set dimensions (e.g., 512x512, 256x256, 128x128, 64x64, 32x32)
4. Download PNG files

### Option 2: Using Figma/Adobe Illustrator
1. Import the SVG
2. Export as PNG at different sizes
3. Common sizes: 16x16, 32x32, 64x64, 128x128, 256x256, 512x512

### Option 3: Command Line (if you have ImageMagick)
```bash
# Install ImageMagick first
brew install imagemagick  # macOS

# Convert to different sizes
convert base47-icon.svg -resize 512x512 base47-icon-512.png
convert base47-icon.svg -resize 256x256 base47-icon-256.png
convert base47-icon.svg -resize 128x128 base47-icon-128.png
convert base47-icon.svg -resize 64x64 base47-icon-64.png
convert base47-icon.svg -resize 32x32 base47-icon-32.png
```

### Option 4: Using Inkscape (Free Desktop App)
1. Download Inkscape: https://inkscape.org/
2. Open SVG file
3. File → Export PNG Image
4. Set width/height and export

## Recommended PNG Sizes

For complete branding package, create these sizes:

- **16x16** - Browser favicon, small UI
- **32x32** - Standard favicon, toolbar icons
- **64x64** - Retina favicon, small logos
- **128x128** - App icons, medium logos
- **256x256** - Large icons, WordPress plugin icon
- **512x512** - High-res icon, marketplace listings
- **1024x1024** - Extra high-res for future use

## WordPress Plugin Icon Sizes

For WordPress.org plugin directory, you need:
- **icon-128x128.png** - Required
- **icon-256x256.png** - Required (Retina)

## Using as Favicon

For website favicon, you can use the SVG directly:
```html
<link rel="icon" type="image/svg+xml" href="base47-icon.svg">
```

Or convert to ICO format for broader compatibility.

## Color Customization

The SVG files can be easily customized:
- Open in any text editor
- Change `fill="#2271b1"` to your brand color
- Change `fill="white"` for different color schemes

## Soft UI Brand Colors

Your Base47 icons use the Soft UI color palette:

**Primary Colors:**
- **Orange:** `#fb6340` - Main accent color (top bar, top-right block)
- **Blue:** `#5e72e4` - Primary color (left column, bottom-right block)

**Gradient Colors (gradient version):**
- Orange gradient: `#fb6340` → `#f5365c`
- Blue gradient: `#5e72e4` → `#825ee4`

**Text/Dark:**
- Dark text: `#32325d` (Soft UI dark)

**Design Features:**
- Border radius: `rx="1.5"` to `rx="2"` (rounded corners - Soft UI style)
- Two-tone color scheme (orange + blue)
- Clean, modern, friendly appearance

---

**Created:** December 10, 2024  
**Format:** SVG (scalable vector graphics)  
**License:** Custom for Base47 use
