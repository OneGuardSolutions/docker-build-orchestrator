# Docker Build Orchestrator

Simple orchestrator for building docker images

## Installation

Download latest release and make it executable:
```bash
$ curl -L -o ./dobr https://github.com/OneGuardSolutions/docker-build-orchestrator/releases/download/0.1.0-beta1/dobr
$ chmod +x ./dobr
```

## Repository structure

Each `Dockerfile` resides in a directory structure in format `<repository>/<tag>/Dockerfile`.
All repositories *must have* configuration file in `<repository>/repository.conf`.

## Repository configuration

Following configuration options are available:
- *namespace* `required` - image namespace including optional custom registry host and port in format
  `<registry_host>:<registry_port>/<owner>}`, if no custom registry is supplied,
  docker will asume `docker.io` (Docker Hub)
- *order* - comma-separated priority list,
  tags listed here will be built in the specified order before those not listed
<!-- Comming soon
- *aliases* - comma-separated list od alias mappings in format `<alias>:<target>`,
  currently only tags are supported as alias targets,
  example: `latest:2,edge:3`
-->

Example:
```
# php/repository.conf
namespace=oneguard
order=7.2-fpm,7.2-fpm-dev
```
<!-- Comming soon
```
# php/repository.conf
namespace=oneguard
order=7.2-fpm
aliases=fpm:7.2-fpm,fpm-dev:7.2-fpm-dev,nginx:7.2-nginx
```
-->

## Future features

- automatic alias creation
- automatic order determination
- determining the need to rebuild the image by FROM image update & source change detection (optional)
