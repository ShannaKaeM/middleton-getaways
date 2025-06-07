# TW4 + Windpress Documentation System
*Complete documentation for Tailwind CSS v4 and Windpress integration*

## 📚 Documentation Overview

This directory contains comprehensive documentation for implementing Tailwind CSS v4 with the Windpress WordPress plugin for the Middleton Getaways project.

### Core Documentation Files

| File | Purpose | Audience |
|------|---------|----------|
| **[TW4-COMPLETE-UPDATED-REFERENCE.md](./TW4-COMPLETE-UPDATED-REFERENCE.md)** | Complete TW4 reference based on official docs | Developers |
| **[WINDPRESS-COMPLETE-REFERENCE.md](./WINDPRESS-COMPLETE-REFERENCE.md)** | Complete Windpress plugin documentation | Developers |
| **[INTEGRATION-GUIDE.md](./INTEGRATION-GUIDE.md)** | Practical implementation guide for Middleton Getaways | Implementation |

## 🎯 Quick Start

### For Developers
1. Start with **TW4-COMPLETE-UPDATED-REFERENCE.md** to understand TW4 concepts
2. Review **WINDPRESS-COMPLETE-REFERENCE.md** for plugin capabilities
3. Follow **INTEGRATION-GUIDE.md** for project-specific implementation

### For Implementation
1. Go directly to **INTEGRATION-GUIDE.md** for step-by-step setup
2. Reference other docs as needed for deeper understanding

## 🏗️ Architecture Overview

### Technology Stack
- **Tailwind CSS v4** - CSS-first configuration with modern directives
- **Windpress Plugin** - Zero-build WordPress integration
- **WordPress + Blocksy** - Theme foundation
- **GenerateBlocks** - Primary page builder

### Project Structure
```
docs/tw4-windpress-system/          # Generic documentation (reusable)
├── README.md                       # This overview
├── TW4-COMPLETE-UPDATED-REFERENCE.md
├── WINDPRESS-COMPLETE-REFERENCE.md
└── INTEGRATION-GUIDE.md

app/.../mi-design-system/            # Project-specific implementation
├── mi-ds-guide.md                  # Complete implementation guide
├── mi-ds-theme.json-example        # WordPress theme.json template
└── mi-ds-windpress-main-example.css # Windpress CSS template
```

## 📋 Implementation Status

### ✅ Completed
- [x] Complete TW4 documentation review
- [x] Windpress integration documentation
- [x] Brand color system definition
- [x] Component architecture planning
- [x] WordPress integration strategy

### 🔄 Next Steps
- [ ] Implement main.css with brand tokens
- [ ] Set up Windpress plugin
- [ ] Create component utilities
- [ ] Build WordPress theme.json integration
- [ ] Test GenerateBlocks integration

## 📁 Related Project Files

### Theme Implementation
- `app/public/wp-content/themes/middleton-getaways-blocksy/mi-design-system/mi-ds-guide.md` - Complete implementation guide
- `app/public/wp-content/themes/middleton-getaways-blocksy/mi-design-system/mi-ds-theme.json-example` - WordPress theme.json template
- `app/public/wp-content/themes/middleton-getaways-blocksy/mi-design-system/mi-ds-windpress-main-example.css` - Windpress CSS template

## 🎨 Brand System

### Color Palette
- **Primary:** Sage Green (#869648) - Natural, calming
- **Secondary:** Sand (#d4a574) - Warm, welcoming  
- **Accent:** Stone (#8b7355) - Earthy, grounded

### Design Philosophy
- Clean, modern aesthetic
- Nature-inspired color palette
- Mobile-first responsive design
- Accessibility-focused implementation

---

*This documentation system provides everything needed to implement a modern, maintainable TW4 design system with WordPress integration.*
