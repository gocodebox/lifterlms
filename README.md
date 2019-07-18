<img src=".github/lifterlms-logo.png" alt="LifterLMS" width="300">

[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/lifterlms.svg)](https://wordpress.org/plugins/lifterlms/)
[![WordPress](https://img.shields.io/wordpress/v/lifterlms.svg)](https://wordpress.org/plugins/lifterlms/)
[![WordPress rating](https://img.shields.io/wordpress/plugin/r/lifterlms.svg)](https://wordpress.org/support/plugin/lifterlms/reviews/)
[![WordPress](https://img.shields.io/wordpress/plugin/dt/lifterlms.svg)](https://wordpress.org/plugins/lifterlms/advanced/)
[![Wordpress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/lifterlms.svg)](https://wordpress.org/plugins/lifterlms/)
[![CircleCI](https://circleci.com/gh/gocodebox/lifterlms/tree/master.svg?style=svg)](https://circleci.com/gh/gocodebox/lifterlms/tree/master)
[![Code Climate](https://codeclimate.com/github/gocodebox/lifterlms/badges/gpa.svg)](https://codeclimate.com/github/gocodebox/lifterlms)
[![Test Coverage](https://codeclimate.com/github/gocodebox/lifterlms/badges/coverage.svg)](https://codeclimate.com/github/gocodebox/lifterlms/coverage)
[![Slack](https://img.shields.io/badge/chat-on%20slack-blue.svg)](https://lifterlms.com/slack)
[![All Contributors](https://img.shields.io/badge/all_contributors-29-orange.svg?style=flat-square)](#contributors)

[LifterLMS](https://lifterlms.com), a WordPress LMS Solution: Easily create, sell, and protect engaging online courses.


### [Changelog](./CHANGELOG.md)


### Documentation
+ [https://lifterlms.com/docs/](https://lifterlms.com/docs/)


### Getting Help and Support Support

GitHub is for bug reports and contributions only! If you have a support question or a request for a customization this is not the right place to post it. Please refer to [LifterLMS Support](https://lifterlms.com/my-account/my-tickets) or the [community forums](https://wordpress.org/support/plugin/lifterlms). If you're looking for help customizing LifterLMS, please consider hiring a [LifterLMS Expert](https://lifterlms.com/docs/do-you-have-any-recommended-developers-who-can-modifycustomize-lifterlms/).


### Reporting a Bug

Bugs can be reported at [https://github.com/gocodebox/lifterlms/issues/new](https://github.com/gocodebox/lifterlms/issues/new).

Security issues and vulnerabilities should be responsibly disclodes. Please see our [Security Policy](.github/SECURITY.md) for details on disclosing a security vulnerability to us.

Before reporting a bug, [search existing issues](https://github.com/gocodebox/lifterlms/issues) and ensure you're not creating a duplicate. If the issue already exists you can add your information to the existing report.

Also check our [known issues and conflicts](https://lifterlms.com/doc-category/lifterlms/known-conflicts/) for possible resolutions.

### Installing for Production Usage

If you clone or download this repo directly it will not run as a plugin inside WordPress! Installable production releases are available in on the [Releases tab](https://github.com/gocodebox/lifterlms/releases). You can get the latest stable release from [WordPress.org](https://downloads.wordpress.org/plugin/lifterlms.zip)

### Installing for Development

1. Composer
  + `curl -sS https://getcomposer.org/installer | php`
  + `php composer.phar install`

2. Node
  + Install node
  + Install npm
  + `npm install --global gulp`
  + `npm install`

### Running phpcs

Use the shorthand composer script to run phpcs against all PHP files.

+ `composer run-script phpcs`

Alternatively access the executable:

+ `./vendor/bin/phpcs path/to/file.php`

To see errors only (no warnings):

+ `./vendor/bin/phpcs -n path/to/file.php`

To see all options:

+ `./vendor/bin/phpcs -h`


### Running phpcbf

+ `./vendor/bin/phpcbf` to run on all php files
+ `./vendor/bin/phpcbf path/to/file.php` to run on a specific file


### Contributing [![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](.github/CONTRIBUTING.md)

Interested in contributing to LifterLMS? We'd love to have your contributions. Read our contributor's guidelines [here](.github/CONTRIBUTING.md).


### Contributors

Thanks goes to these wonderful people ([emoji key](https://github.com/kentcdodds/all-contributors#emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore -->
| [<img src="https://avatars0.githubusercontent.com/u/1290739?v=4" width="100px;"/><br /><sub><b>Thomas Patrick Levy</b></sub>](http://gocodebox.com)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=thomasplevy "Code") [🎨](#design-thomasplevy "Design") | [<img src="https://avatars1.githubusercontent.com/u/1739834?v=4" width="100px;"/><br /><sub><b>Saurabh Shukla</b></sub>](https://github.com/actual-saurabh)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=actual-saurabh "Code") | [<img src="https://avatars0.githubusercontent.com/u/47434271?v=4" width="100px;"/><br /><sub><b>nrherron92</b></sub>](https://github.com/nrherron92)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Anrherron92 "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=nrherron92 "Code") | [<img src="https://avatars3.githubusercontent.com/u/7689242?v=4" width="100px;"/><br /><sub><b>Rocco Aliberti</b></sub>](https://github.com/eri-trabiccolo)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Aeri-trabiccolo "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=eri-trabiccolo "Code") | [<img src="https://avatars0.githubusercontent.com/u/5050601?v=4" width="100px;"/><br /><sub><b>Mark Nelson</b></sub>](http://therealmarknelson.com)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=therealmarknelson "Code") [🎨](#design-therealmarknelson "Design") | [<img src="https://avatars3.githubusercontent.com/u/4542049?v=4" width="100px;"/><br /><sub><b>Petar Smolic</b></sub>](http://psmolic.com)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=PSmolic "Code") | [<img src="https://avatars1.githubusercontent.com/u/8673706?v=4" width="100px;"/><br /><sub><b>Benjamin R. Matthews</b></sub>](https://bmatt468.com)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=bmatt468 "Code") |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| [<img src="https://avatars0.githubusercontent.com/u/1678457?v=4" width="100px;"/><br /><sub><b>Maximiliano Rico</b></sub>](https://github.com/MaximilianoRicoTabo)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=MaximilianoRicoTabo "Code") | [<img src="https://avatars1.githubusercontent.com/u/1697968?v=4" width="100px;"/><br /><sub><b>Andreas Blumberg</b></sub>](https://github.com/andreasblumberg)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=andreasblumberg "Code") | [<img src="https://avatars2.githubusercontent.com/u/403283?v=4" width="100px;"/><br /><sub><b>Daniele Scasciafratte</b></sub>](http://www.mte90.net)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3AMte90 "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=Mte90 "Code") [🤔](#ideas-Mte90 "Ideas, Planning, & Feedback") | [<img src="https://avatars0.githubusercontent.com/u/487629?v=4" width="100px;"/><br /><sub><b>Joost de Valk</b></sub>](http://yoast.com/)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=jdevalk "Code") | [<img src="https://avatars3.githubusercontent.com/u/584693?v=4" width="100px;"/><br /><sub><b>Anton Timmermans</b></sub>](https://github.com/atimmer)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=atimmer "Code") [🤔](#ideas-atimmer "Ideas, Planning, & Feedback") | [<img src="https://avatars2.githubusercontent.com/u/10199798?v=4" width="100px;"/><br /><sub><b>Nikola Pasic</b></sub>](http://nikola.pasic.rs)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=nikolapasic "Code") | [<img src="https://avatars0.githubusercontent.com/u/190159?v=4" width="100px;"/><br /><sub><b>Andrea Barghigiani</b></sub>](https://skillsandmore.org)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3AAndreaBarghigiani "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=AndreaBarghigiani "Code") |
| [<img src="https://avatars3.githubusercontent.com/u/3424234?v=4" width="100px;"/><br /><sub><b>Tyler Kemme</b></sub>](https://tylerkemme.com)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=tpkemme "Code") | [<img src="https://avatars3.githubusercontent.com/u/15683967?v=4" width="100px;"/><br /><sub><b>Dinesh Chouhan</b></sub>](http://dineshchouhan.com)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Adineshchouhan "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=dineshchouhan "Code") [🎨](#design-dineshchouhan "Design") [🤔](#ideas-dineshchouhan "Ideas, Planning, & Feedback") | [<img src="https://avatars2.githubusercontent.com/u/9405480?v=4" width="100px;"/><br /><sub><b>hovpoghosyan</b></sub>](https://github.com/hovpoghosyan)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=hovpoghosyan "Code") | [<img src="https://avatars0.githubusercontent.com/u/37841388?v=4" width="100px;"/><br /><sub><b> Pavel Yumashev</b></sub>](https://github.com/yumashev)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Ayumashev "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=yumashev "Code") | [<img src="https://avatars2.githubusercontent.com/u/249506?v=4" width="100px;"/><br /><sub><b>Matt Halliday</b></sub>](http://matthalliday.ca)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=matthalliday "Code") | [<img src="https://avatars1.githubusercontent.com/u/837136?v=4" width="100px;"/><br /><sub><b>Terence Eden</b></sub>](https://shkspr.mobi/blog/)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Aedent "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=edent "Code") | [<img src="https://avatars2.githubusercontent.com/u/2222249?v=4" width="100px;"/><br /><sub><b>sujaypawar</b></sub>](https://github.com/sujaypawar)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Asujaypawar "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=sujaypawar "Code") [🎨](#design-sujaypawar "Design") [🤔](#ideas-sujaypawar "Ideas, Planning, & Feedback") |
| [<img src="https://avatars2.githubusercontent.com/u/5949352?v=4" width="100px;"/><br /><sub><b>Phil Webster</b></sub>](https://github.com/philwp)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=philwp "Code") | [<img src="https://avatars0.githubusercontent.com/u/30046495?v=4" width="100px;"/><br /><sub><b>Adam Williams</b></sub>](https://github.com/README1ST)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3AREADME1ST "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=README1ST "Code") | [<img src="https://avatars0.githubusercontent.com/u/1916064?v=4" width="100px;"/><br /><sub><b>Yojance Rabelo</b></sub>](https://www.wpsuperstar.com/)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Ayojance "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=yojance "Code") | [<img src="https://avatars3.githubusercontent.com/u/11303423?v=4" width="100px;"/><br /><sub><b>Chris Ballard</b></sub>](https://github.com/unt01d)<br />[⚠️](https://github.com/gocodebox/lifterlms/commits?author=unt01d "Tests") | [<img src="https://avatars3.githubusercontent.com/u/796639?v=4" width="100px;"/><br /><sub><b>Travis Northcutt</b></sub>](http://memberup.co/)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=tnorthcutt "Code") | [<img src="https://avatars0.githubusercontent.com/u/2005352?v=4" width="100px;"/><br /><sub><b>Jip</b></sub>](http://twitter.com/moorscode)<br />[💻](https://github.com/gocodebox/lifterlms/commits?author=moorscode "Code") | [<img src="https://avatars1.githubusercontent.com/u/5377968?v=4" width="100px;"/><br /><sub><b>James Richards</b></sub>](http://vistacast.com)<br />[📖](https://github.com/gocodebox/lifterlms/commits?author=pondermatic "Documentation") |
| [<img src="https://avatars2.githubusercontent.com/u/1119590?v=4" width="100px;"/><br /><sub><b>Andrew Vaughan</b></sub>](https://andrewvaughan.io)<br />[🐛](https://github.com/gocodebox/lifterlms/issues?q=author%3Aandrewvaughan "Bug reports") [💻](https://github.com/gocodebox/lifterlms/commits?author=andrewvaughan "Code") |
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/kentcdodds/all-contributors) specification.


### Partners

<table>
  <tr>
    <td>

[<img src="https://raw.githubusercontent.com/gocodebox/lifterlms/master/.github/sponsors/browserstack-logo.png" height="80" alt="BrowserStack">](https://www.browserstack.com/)

[BrowserStack](https://www.browserstack.com/) helps us ensure LifterLMS looks great and works on every imaginable browser and device.
    </td>
    <td>
[<img src="https://raw.githubusercontent.com/gocodebox/lifterlms/master/.github/sponsors/stagingpilot-logo.png" height="80" alt="StagingPilot">](https://stagingpilot.com/)

[StagingPilot](https://stagingpilot.com/) helps us automate acceptance testing to ensure LifterLMS remains compatible with popular WordPress themes and plugins.
    </td>
  </tr>
</table>
