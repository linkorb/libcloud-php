# libcloud-php

PHP library for interacting with many of the popular cloud service providers using a unified API.

<img src="http://upr.io/EPxv4p.png" style="width: 100%" />


## Introduction

This project aims to simplify the usage of cloud services by PHP developers. The scope of the project includes the following service types:

* **Compute**: Create, Restart, Destroy and provision compute nodes in the cloud
* **DNS**: Update DNS configurations at DNS providers
* **ObjectStorage**: store objects in the cloud
* **LoadBalancer**: Manage load balancer configurations at your IaaS provider
* **Container**: Launch containers and manage images at CaaS providers

This project is inspired by the awesome Python library [libcloud](http://libcloud.apache.org) with the same name. We try to follow the design as closely as possible. We keep method names the same as the Python project, but updating it for [PSR2](http://www.php-fig.org/psr/psr-2/) compliance.


## Using the library in your project

libcloud-php is available on [packagist](https://packagist.org/packages/linkorb/libcloud-php). To use it, add the following to your `composer.json` file, and run `composer update`:

```json
{
    "require": {
        "linkorb/libcloud": "~1.0"
    }
}
```

## Examples

Please refer to the [example directory](example/) for example code by service type (compute, dns, etc).

## Contributing

Ready to build and improve on this repo? Excellent!
Go ahead and fork/clone this repo and we're looking forward to your pull requests!
Please take a look at the python version in order to follow this design, constants and method names as closely as possible, while updating it in the php-way. 

## TODO / Next steps:

We're planning on the following features. Please feel free to send us a PR if you're interested in helping out!

* [ ] Extend the support of this library for more providers and services
* [ ] Implement ObjectStorage interfaces and adapters based on [linkorb/objectstorage](https://github.com/linkorb/objectstorage)
* [ ] Implement LoadBalancer and Container interfaces

## License

MIT. Please refer to the [license file](LICENSE) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
