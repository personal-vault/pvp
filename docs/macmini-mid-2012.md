---
title: MacMini 2012
layout: default
parent: Setup
status: draft
---
# MacMini Intel-based Mid-2012

**THIS PAGE IS DRAFT**

This is my first hardware I'm trying this on, as it's what I already have, and I'm looking to reuse equipment as much as possible.

Specs:

* Mid-2012 Apple MacMini
* 16 GB RAM
* 256 SSD + ??? HDD
* OS: MacOS High Sierra

The MacMini is repurposed to work as a server, therefore remote administration should be possible:

* Enable File Sharing
* Enable Remote Connection
* Enable Remote Desktop

The latest Docker Desktop no longer works on High Sierra, therefore, you'll have to download an older version from the [Release Notes page](https://docs.docker.com/desktop/release-notes/).

Version 3.6.0 is the latest one that seems to work on High Sierra, but even with this one, it complains that the Docker helper tool cannot be installed on such an old OS. Docker Desktop does seem to start though, so your mileage may vary.



## Enable Remote Desktop

```shell
$ sudo /System/Library/CoreServices/RemoteManagement/ARDAgent.app/Contents/Resources/kickstart \
 -activate -configure -access -on \
 -configure -allowAccessFor -allUsers \
 -configure -restart -agent -privs -all
```

To disable:

```shell
sudo /System/Library/CoreServices/RemoteManagement/ARDAgent.app/Contents/Resources/kickstart -deactivate -configure -access -off
```

Then you can connect to it with the Screen Sharing app.
