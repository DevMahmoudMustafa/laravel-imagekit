# Changelog - ImageKit

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
and the project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2025-01-XX
### 🎉 Initial Release (First Public Version)

This is the first official public release of **ImageKit**, a Laravel package for advanced and fluent image processing.

### ✨ Highlights of This Release

#### 🚀 Core Features
- Image upload & validation
- Single & multi-size image resizing
- Image compression with adjustable quality
- Watermarking (file path or uploaded file)
- Custom storage disk support (local, public, S3, GCS, Azure, custom)
- Dynamic file naming strategies (default, UUID, hash, timestamp, custom)

#### 🖼️ Gallery Support
- Multiple image upload
- Batch processing
- Auto-generated metadata
- Support for alt text (single or per-image)

#### 🔧 Deletion Tools
- Delete single image
- Delete all size variants
- Batch deletion for galleries

#### 📤 Image Retrieval
- Get image URL
- Get storage path
- Temporary URLs for cloud
- Display image as HTTP response
- Download image

#### 🧩 Fluent API
- Clean, readable, chainable methods
- Multiple aliases for developer convenience

#### 📢 Events System
- `ImageSaving`
- `ImageSaved`
- `ImageDeleted`

#### 🛡️ Error Handling
- Validation errors with descriptive messages
- Custom exception classes
- Graceful failure handling

---

## Notes

- This is the **first stable release**, no breaking changes exist.
- Future versions will include presets, queue processing, logging, dashboard, and more.

---

**Release Status:** Stable  
**Version:** 1.0.0  
