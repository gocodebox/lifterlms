WordPress.org Plugin Repository Content and Assets
==================================================

This directory contains the text content and assets used on the LifterLMS plugin listing on [WordPress.org Plugin Repository](https://wordpress.org/plugins/lifterlms/). 

## README

The [readme](./readme) directory contains the markdown files representing the sections (and tabs) used on the listing.

These files are combined during a build step prior distribution and output as the [readme.txt](../readme.txt) file distributed with the LifterLMS plugin. Generally we do not ship updates for changes to the readme directory. These changes will be included in the next release which contains code changes.

The files are prepended with numbers to preserve their order when programmatically combined.

The command to build the readme file is `npm run dev readme`. See full documentation of the command in the [@lifterlms/dev package reference](https://github.com/gocodebox/lifterlms/tree/trunk/packages/dev#readme).

### File Parts

+ [01-header.md](./readme/01-header.md): The readme header containing the listing's display title, meta data, and a short description.
+ [05-description.md](./readme/05-description.md): The main listing "Details" tab.
+ [10-installation.md](./readme/10-installation.md): The contents of the "Installation" tab
+ [15-faqs.md](./readme/15-faqs.md): A list of frequently asked questions. This is listed at the bottom of the "Details" tab on the listing page.
+ [20-screenshots.md](./readme/15-faqs.md): An ordered list of screenshot captions. Each caption should correspond with a screenshot in the [assets directory](./assets). A screenshot with the filename `screenshot-5.png` corresponds to the item 5 in the screenshot list. 
+ [25-changelog.md](./readme/25-changelog.md): an auto-generated changelog containing the latest 10 changelog entries from the main [CHANGELOG.md](../CHANGELOG.md) file.

### Merge Codes

Various merge codes are available for use in the readme file parts. See the [@lifterlms/dev package reference](https://github.com/gocodebox/lifterlms/tree/trunk/packages/dev#readme) for merge code documentation.

## Assets

The [assets](./assets) directory contains the images used in the listing: banners, icons, and screenshots.

See also: [How Your Plugin Assets Work](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/).

Assets are manually synced to WordPress.org via SVN @thomasplevy and will be updated in conjunction with the next release following their update in this directory.