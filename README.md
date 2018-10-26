# Docker Build Orchestrator

[![Build Status](https://travis-ci.com/OneGuardSolutions/docker-build-orchestrator.svg?branch=master)](https://travis-ci.com/OneGuardSolutions/docker-build-orchestrator)
[![Coverage Status](https://coveralls.io/repos/github/OneGuardSolutions/docker-build-orchestrator/badge.svg)](https://coveralls.io/github/OneGuardSolutions/docker-build-orchestrator)
[![Maintainability](https://api.codeclimate.com/v1/badges/0652bcdaf9909daf9d36/maintainability)](https://codeclimate.com/github/OneGuardSolutions/docker-build-orchestrator/maintainability)
[![GitHub (pre-)release](https://img.shields.io/github/release/OneGuardSolutions/docker-build-orchestrator/all.svg)](https://github.com/OneGuardSolutions/docker-build-orchestrator/releases)
[![GitHub license](https://img.shields.io/github/license/OneGuardSolutions/docker-build-orchestrator.svg)](https://github.com/OneGuardSolutions/docker-build-orchestrator/blob/master/LICENSE)

Simple orchestrator for building docker images

**Warning:** This version is **in early stage of development**.
For working version see [v1](https://github.com/OneGuardSolutions/docker-build-orchestrator/tree/v1) branch.

## Features

- automatic build order determination based on `FROM` statements in `Dockerfile`
- multistage build ordering support
- cyclic dependency detection
- automatic alias creation

## Installation

Download the latest [release](https://github.com/OneGuardSolutions/docker-build-orchestrator/releases/latest)
and its public key, and make it executable.

## Repository structure

Each `Dockerfile` resides in a directory structure in format `<root>/<repository>/<tag>/Dockerfile`.
All repositories *may have* configuration file in `<repository>/repository.yaml`, or `<repository>/repository.yml`.
If both `repository.yaml` and `repository.yml` are present `repository.yaml` has priority.

## Repository configuration

Following configuration options are available:
- *registry* `optional` - image registry, in form of `host[:port]`;
    if nor provided, the default Docker registry will be used
- *namespace* `optional` - image namespace (owner username);
    if not is provided, `library` (top level repository) is assumed
- *aliases* `optional` - alias mappings, keys are used as alias name,
    and values as named image (tag) reference

Example:
```yaml
# <root>/php/repository.yaml
registry: 'docker.example.io'
namespace: 'oneguard'
aliases:
  latest: '7.2'
  dev: '7.2-dev'
```

## Future features ideas

- Determining the need to rebuild the image
  - base image update check
  - source change detection check
  - custom checks (e.g. used library has new release)
- Custom image metadata support - could be used for with custom checks
- WebHooks

## License

This software is under the MIT license. See the complete license attached with the source code:

> [LICENSE](LICENSE)

## Reporting an issue or requesting a feature

Issues and feature requests are tracked in the
[Github issue tracker](https://github.com/OneGuardSolutions/docker-build-orchestrator/issues).
