Contributing to LifterLMS
=========================

We welcome and encourage contributions from the community. If you'd like to contribute to LifterLMS there are a few ways to do so. Here's our guidelines for contributions:

*Please Note GitHub is for bug reports and contributions only! If you have a support question or a request for a customization this is not the right place to post it. Please refer to [LifterLMS Support](https://lifterlms.com/my-account/my-tickets) or the [community forums](https://wordpress.org/support/plugin/lifterlms). If you're looking for help customizing LifterLMS, please consider hiring a [LifterLMS Expert](https://lifterlms.com/docs/do-you-have-any-recommended-developers-who-can-modifycustomize-lifterlms/).*


### Ways to Contribute

+ [Submit bug and issues reports](#reporting-a-bug-or-issue)
+ [Contribute new features](#contributing-new-features)
+ [Contribute new code or bug fixes / patches](#contributing-code)
+ [Translate and localize LifterLMS](#contribute-translations)


### Reporting a Bug or Issue

Bugs and issues can be reported at [https://github.com/gocodebox/lifterlms/issues/new/choose](https://github.com/gocodebox/lifterlms/issues/new).

Before reporting a bug, [search existing issues](https://github.com/gocodebox/lifterlms/issues) and ensure you're not creating a duplicate. If the issue already exists you can add your information to the existing report.

Also check our [known issues and conflicts](https://lifterlms.com/doc-category/lifterlms/known-conflicts/) for possible resolutions.

### Contributing New Features

When contributing new features please communicate with us to ensure this is a feature we're interested in having added to LifterLMS before you start coding it.

First check if we already have a feature request or proposal for the feature you're interested in developing. Take a look at our existing feature requests here in [GitHub](https://github.com/gocodebox/lifterlms/issues?utf8=%E2%9C%93&q=is%3Aissue+label%3A%22type%3A+feature+request%22) and on our [Feature Request voting board](https://trello.com/b/egC72ZZS/lifterlms-road-map-and-feature-voting).

If you can't find an existing feature request you should propose it by opening a new [feature request issue](https://github.com/gocodebox/lifterlms/issues/new?template=Feature_Request.md). In the issue we'll discuss your feature  before you start working on it.

LifterLMS is a project that services a great many users. A feature which is attractive to a small number of users may create confusion for other users. These features may be better offered as a feature plugin instead of code in the core. In this scenario we'd be happy to help advise you on how to best develop and launch your feature as a plugin on WordPress.org! We'll even help market your add-on after you launch.

### Contributing Code

+ Fork the repository on GitHub.
+ [Install LifterLMS for development](../docs/installing.md).
+ Create a new branch from the 'trunk' branch.
+ Make the changes to your forked repository.
+ Ensure you stick to our [coding standards](https://github.com/gocodebox/lifterlms/blob/trunk/docs/coding-standards.md) and have properly documented new and updated functions, methods, actions, and filters following our [documentation standards](https://github.com/gocodebox/lifterlms/blob/trunk/docs/documentation-standards.md).
+ Run PHPCS and ensure the output has no errors. We **will** reject pull requests if they fail codesniffing.
+ Ensure new code doesn't break existing tests and add new code should aim to have 100% code coverage. See the [testing guide](https://github.com/gocodebox/lifterlms/blob/trunk/tests/phpunit/README.md) to get started with testing and let us know if you want help writing tests, we're happy to help!
+ When making changes to (S)CSS and Javascript files, you should only modify the source files. The compiled and minified files *should not be committed* or included in your PR.
+ When committing, reference your issue (if present) and include a note about the fix. Use [GitHub auto-references](https://help.github.com/en/articles/autolinked-references-and-urls).
+ Push the changes to your fork
+ Submit a pull request to the 'dev' branch of the LifterLMS repo.
+ We'll review all pull requests, and make suggestions and changes if necessary. We're newly open source and supporting users and customers and our own internal pull requests and releases will take priority over pull requests from the community. Please be patient!


### Contribute Translations

All translations to LifterLMS can be made via our GlotPress project at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/lifterlms).

Anyone can contribute translations. All you need is to login to your wordpress.org account. If you have questions about how to submit translations please refer to the [Translator's Handbook](https://make.wordpress.org/polyglots/handbook/).

We're always seeking Translation Editors who can manage and approve translations for their locale. If you're interested in becoming a translation editor for your locale please [review the documentation about translations here](https://lifterlms.com/docs/how-can-i-contribute-translations-to-lifterlms/).


### Need Help Getting Started as a Contributor?

A number of resources are available for first time contributors:

+ Join our [LifterLMS Community Slack Channel](https://lifterlms.com/slack) and hop into the `#developers` channel. Our core contributors and maintainers are there to help out and answer questions.
+ Check out the [LifterLMS Community Events Calendar](https://lifterlms.com/community-events/) for opportunities to interact with other contributors.
+ Check out [this tutorial](https://www.digitalocean.com/community/tutorials/how-to-create-a-pull-request-on-github) on how to submit pull requests on GitHub.
+ Grab an issue marked tagged as a [`good first issue`](https://github.com/gocodebox/lifterlms/issues?q=is%3Aissue+is%3Aopen+label%3A%22good+first+issue%22)
