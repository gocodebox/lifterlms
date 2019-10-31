Releasing LifterLMS Builds
==========================

This document outlines the workflow used by LifterLMS core maintainers to build and publish LifterLMS releases.

## 1. Prepare the Release

1. Ensure the release passes all automated testing. Use `composer run-script tests-run`.
2. Ensure the release passes coding standard checks. Use `composer run-script check-cs`.

_Note: files in the `tmp` directory used during tests are currently parsed by the certain gulp tasks. Remove the `tmp` directory before proceeding to the next steps._


## 2. Build the Release

1. Update `CHANGELOG.md` to have the release version and date.
2. Update `CHANGELOG.md` to note all changes relevant to end users and developers.
3. Update `CHANGELOG.md` to record _any changes_ to any files in the `template` directory.
4. Build a new release locally:

    A. Replace all `[version]` tags with the release version number: run `gulp versioner -V $version`. `$version` should either be:
        a. The full version number, eg: `3.25.3`, `3.25.3-beta.1`, etc...
        b. A keyword string `major`, `minor`, or `patch` to increment to the code base to the next version.
    B. Compile all static assets (`.js`, `.css`), localization files (`.pot`) and dynamically generated classes: run `gulp build`.
    C. Create a zip file: run `gulp zip`.

5. Unzip the build and confirm that the main plugin file's `Version` header tag has been properly incremented.
6. Install and activate the zip file on a temporary sandbox site.

    A. Run the setup wizard.
    B. Import sample course
    C. Enroll a student into the course.
    D. Complete a lesson.

    _This manual testing ensures no (unlikely but possible) errors occurred in the build steps above._


## 3. Publish the Release

The following steps can be performed manually but are best handled using the `gulp publish` task. API credentials are required to perform these steps via the task.

1. Publish to GitHub

    A. Force push the contents of the zip file created in step 2 to the `trunk` branch.
    B. Create a new release using `trunk` as the commit target.

2. Publish to WordPress plugin repository

    A. Create a new tag.
    B. Update the readme.txt file in the `trunk`

3. Publish changelog blog post to make.LifterLMS.com

    A. Title the post "LifterLMS Version `$version`" where `$version` is the release version number.
    B. Copy the relevant section of the changelog and add it as the post content.
    C. Categorize the blog post under "Release Notes"
    D. Tag the blog post "Core"

4. Update version number at LifterLMS.com

    A. Update the "Version" product field to be the release version.

Additional steps for for major and minor releases. Currently these steps can only be performed via SSH and only Thomas has access to do so. In the future these steps will be automated via the `publish` gulp task.

5. Update API documentation at developer.lifterlms.com.
6. Package documentation for Dash and publish to Dash-User-Contributions
