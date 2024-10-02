# TubeMediaConverter

## Changelog:

### v1.74

Added:
- HTTP Proxy support for YouTube requests
  - For proxy config and troubleshooting instructions, see https://github.com/PureDevLabs/TubeMediaConverter/discussions/14
- Better YouTube client name detection

Full Changelog: [v1.73...v1.74](https://github.com/PureDevLabs/TubeMediaConverter/compare/v1.73...v1.74)

---

### v1.73

Fixes:
- Updated "client" used with YouTube player API requests (in response to a new YouTube change)

Full Changelog: [v1.72...v1.73](https://github.com/PureDevLabs/TubeMediaConverter/compare/v1.72...v1.73)

---

### v1.72
- Updated "client" used with YouTube player API requests (in response to a new YouTube change)

#### Updated files
```
README.md
lib/extractors/YouTube.php
```

**Delete "store/software.xml" after updating!**

---

### v1.71
- Added ability to use a "Trusted Session" for authenticating YouTube requests 
  - Trusted Sessions only work when [youtube-trusted-session](https://github.com/PureDevLabs/youtube-trusted-session) is also installed on the SAME server
  - Trusted Session does NOT require any YouTube accounts
  - Trusted Sessions automatically regenerate every 2 hours
  - Trusted Sessions should not require manual, regular maintenance (unlike YouTube login cookies)

#### Updated files
```
README.md
inc/scheduler.php
inc/version.php
lib/TrustedSession.php (new)
lib/extractors/YouTube.php
```

**Remove cookies from "store/ytcookies.txt" after updating!**

**Node.js is now a software requirement! Install via the Config Check utility (by deleting "store/setup.log").**

---

### v1.7
- Rebranded
- Removed Licensing
- Removed Encoding
- Added YouTube cookies support

#### Updated files
```
LICENSE (new)
README.md
app/Languages/de.php
app/Languages/en.php
app/Templates/default-alt/layouts/default/footer.php
app/Templates/default/layouts/default/footer.php
app/Templates/xeon-alt/assets/css/main.css
app/Templates/xeon-alt/assets/sass/main.sass
app/Templates/xeon-alt/layouts/default/footer.php
app/Templates/xeon/assets/css/main.css
app/Templates/xeon/assets/sass/main.sass
app/Templates/xeon/layouts/default/footer.php
docs/index.html
docs/ymckey.sql (delete)
inc/check_config.php
inc/data.php (delete)
inc/dataRequest.php (delete)
inc/error.php
inc/functions.php (delete)
inc/update.php
inc/version.php
index.php
lib/Config.php
lib/Core.php
lib/FFmpeg.php
lib/Remote.php
lib/VideoConverter.php
lib/extractors/Extractor.php
lib/extractors/YouTube.php
store/ytcookies.txt (new)
```

See docs at "docs/index.html"
