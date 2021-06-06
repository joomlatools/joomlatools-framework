# Joomlatools Framework

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7ba6e3e1390b488ca40f3d7458332345)](https://www.codacy.com/gh/joomlatools/joomlatools-pages?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=joomlatools/joomlatools-pages&amp;utm_campaign=Badge_Grade)

## What is Joomlatools Framework?

Joomlatools Framework (codename 'Koowa') is a open-source **component framework**. Developed and maintained by [Joomlatools](http://joomlatoolls.com) with the help of passionate developers from all over the world.

Joomlatools Framework can be installed in Joomla as a plugin and lets you focus on the "business" logic of your extension or can be used standalone. By doing most of the repetitive work for you [boilerplate code][boilerplate] is eliminated which greatly reduces the time
to develop Joomla extensions.

The design pattern based architecture makes your extension more flexible: your extension becomes more **re-usable**,
**replaceable** and more easily **extensible**. Additionally Joomlatools Framework provides you with excellent **out-of-the-box security** features.

Joomlatools Framework uses a [component based architecture](http://en.wikipedia.org/wiki/Component-based_software_engineering)
and includes everything needed to create Joomla extensions according to the [Hierarchical Model-View-Contoller][HMVC]
**(HMVC)** pattern.

## Why Joomlatools Framework?

Joomlatools Framework is built to help developers create **custom** Joomla extensions and tries to solve following problems:

* Provide a solid modern and lean architecture for building extensions.
* Allow extensions to be more flexible and extended easily.

## Who is Joomlatools Framework for ?

Joomlatools Framework is for developers creating custom Joomla extensions. The framework can only be installed using Composer and cannot be installed using the Joomla installer. If you are a developer who wishes to distribute the framework as part of an installable package please [contact us](http://joomlatools.com/contact/) first.

## Production Ready

The framework uses a conservative development approach focused on the lowest common denominator, at time of writing this
is Joomla 3.x and PHP 5.4. The framework is fully forwards compatible with PHP 7.4.

The framework follows the [semantic versioning standard](http://semver.org/). Rest assured : it's designed for stability
and compatibility. We promise that all minor versions will be 100% backwards compatible. Only in major versions backwards
compatibility is not guaranteed.

## Requirements

* Joomla 3.6 or newer
* PHP 7.3 or newer
* MySQL 5.x

## Installation

Go to the root directory of your Joomla installation in command line and execute this command:

```
composer require joomlatools/framework:3.*
```

## Contributing

Joomlatools Framework is an open source, community-driven project. Contributions are welcome from everyone. 
We have [contributing guidelines](CONTRIBUTING.md) to help you get started.

## Contributors

See the list of [contributors](https://github.com/joomlatools/joomlatools-framework/contributors).

## License

Joomlatools Framework is open-source software licensed under the [GPLv3 license](LICENSE.txt).

## Community

Keep track of development and community news.

* Follow [@joomlatoolsdev on Twitter](https://twitter.com/joomlatoolsdev)
* Join [joomlatools/dev on Gitter](http://gitter.im/joomlatools/dev)
* Read the [Joomlatools Developer Blog](https://www.joomlatools.com/developer/blog/)
* Subscribe to the [Joomlatools Developer Newsletter](https://www.joomlatools.com/developer/newsletter/)

[HMVC]: http://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller
[boilerplate]: http://en.wikipedia.org/wiki/Boilerplate_code
