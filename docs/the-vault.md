---
title: The Vault
layout: default
nav_order: 10
parent: Concepts
---
# The Vault

> You favorite music, your personal photos, your notes, your favorite quotes and all your important files, including scanned documents. These are pieces of information that should be able to be stored indefinitely. For 50 or 100 years, especially if you'll still be alive by then.

It's nearly impossible to predict which storage solution will be around in 10 or 30 years, the safest bet it so store it using a popular structure that can be later migrated to modern systems if needed.

Requirements for the storage system:

1. Replicated in multiple locations (no single point of failure, **resilient**).
1. Encrypted at rest (**secure**).
1. Encrypted in transport (**private**).
1. Accessible via a simple, generic protocol (**generic**).

Since we're aiming for generic, for storage systems, the top 3 most basic storage systems might be:

1. File systems, which organize and store files on a computer’s hard drive or other storage devices.
1. Block storage systems, which divide data into fixed-sized blocks and store them on separate physical devices.
1. Object storage systems, which store data as discrete objects with unique identifiers and metadata, and can be accessed over a network using APIs.

## Everything is a file

Even though the blocks storage and object storage might be good options, to keep things as simple as possible, we're going to assume that everything is a file, and use the file system as the basic storage system. It's old and stable enough, easy to understand and to scale, and will probably be around for a long time.

Using the [PESOS] model, users will probably already use big brand services such as Google. Therefore, a good starting point for building a persona vault is downloading all the data in Google via the [Google Takeout] service. This download is a file system download, where all of a user's data is organized in folders and files. Even if some of the files are JSON files that might be stored in an object storage system.

## Drop it anywhere

One of the reasons that organization systems are failing is that the mindset used when we store information is not the same mindset used when we search for the information. Therefore, one essential rule to keep in mind for this system is that it shouldn't matter how you organize the file system. Even more important since organizing one's entire digital life sounds like a complex task that could take a long time and still not be the perfect organization system.

Second reason to be able to drop it anywhere is that we are using various systems that have data organized in various way, so there's no single organizational system that will be suitable for everyone.

The only requirement is that the top-level folder should probably be a username, to facilitate storage of multiple users of an organization/company/family/group.

## Should be mountable

No matter what OS we are using, the simplest way to organize a file system is to mount it locally as a drive.

The storage solution should then have a mountable interface (such as [Samba], [NFS], [iSCSI] or [WebDav]).

## Storage system of choice: ZFS

ZFS began as part of the Sun Microsystems Solaris operating system in 2001[^5].

It ticks all the boxes, being resilient, secure, and generic. One can even rent ZFS storage[^6] without having to trust the owners, thanks to encryption at rest.

With ZFS, we have an ability to use various drives and create a pool of device to form one solid storage with a certain degree of data-distribution

## Other solutions

### S3-compatible object-storage

An object storage system might be a solution, for two reasons:

1. It allows embedding of meta information for each object, this can be important information such as a description of an image that should be stored and processed together with the image.
1. It allows storing many small files without worrying about inodes[^1] and available space.

> **Object storage** is a computer data storage that manages data as objects, as opposed to other storage architectures like file systems which manages data as a file hierarchy, and block storage which manages data as blocks within sectors and tracks.[^2]

Several cloud services support object storage and offer a common interface known as the S3, from [Amazon's S3] service: [Linode Object Storage], [DigitalOcean Spaces], [OVH Cloud], [Seagate Lyve Cloud], [Google Cloud Storage] and even some that you might have not even heard about, like [IONOS S3 Object Storage] or [Vultr Object Storage]. See [S3 Compatible APIs] for a nice list of them.

They all offer object storage in the cloud, and provide an S3 compatible API interface to manage the data.

As many cloud services provide S3-compatible object storage, your data will be in their possession, not yours, so it's important to use a trustworthy service and/or, even more secure, to encrypt the data at rest. Most of them already have automated backups and replication, therefore they are considered to be reliable and resilient for as long as the owning company is in business. Occasionally there are breaches, security and maintenance should be a constant process.

For on-the-premises storage, there are a few options as well that include an S3-compatible API interface:

* [Ceph] provides a unified storage service with object, block, and file interfaces from a single cluster built from commodity hardware components.
* [Min.IO] offers high-performance, S3 compatible object storage. Native to Kubernetes, MinIO is the only object storage suite available on every public cloud, every Kubernetes distribution, the private cloud and the edge. MinIO is software-defined and is 100% open source under GNU AGPL v3.
* [Riak] provides a cost-effective solution that’s highly available, scalable and simple to use for storing all your images, text, video, documents, database backups and software binaries. Optimized for public, private or hybrid clouds, Riak S2 is Amazon S3- and OpenStack Swift-compatible, has robust APIs, and scales easily to handle petabytes of data using commodity software that provides near-linear performance increases as capacity is added.
* [LakeFS] is an open source data version control for data lakes. It enables zero copy Dev / Test isolated environments, continuous quality validation, atomic rollback on bad data, reproducibility, and more.

Depending on your needs and expertise, either of the above _should_ be fine.

A local storage system that has S3 can probably be mounted locally via various tools such as [s3fs-fuse] ([MinIO S3FS example](https://github.com/nitisht/cookbook/blob/master/docs/s3fs-fuse-with-minio.md)), however performance wise it's not a great idea.

## Online services sync

Services that have an API can be imported and synchronized automatically.

## Manually importing data

Some services (such as Whatsapp) don't have an API, and thus must be imported manually. The import procedure might be automated to some extent, but exporting is usually done personally and manually.

The big brands have options to manually request a download of all one's data:

- [Google Takeout]
- Facebook: Download your Information[^3]
- Apple: Get a copy of your data[^4]

The importing procedure must be idempotent, in such a way that importing every X months would not generate a completely different copy, but will add up to the already imported archive.

----
[^1]: [wikipedia.org/wiki/Inode](https://en.wikipedia.org/wiki/Inode)
[^2]: [wikipedia.org/wiki/Object_storage](https://en.wikipedia.org/wiki/Object_storage)
[^3]: [www.facebook.com/help/212802592074644](https://www.facebook.com/help/212802592074644)
[^4]: [support.apple.com/en-us/HT208502](https://support.apple.com/en-us/HT208502)
[^5]: [encyclopedia.pub/entry/35912](https://encyclopedia.pub/entry/35912)
[^6]: [zfs.rent](https://zfs.rent/)

[Amazon's S3]: https://docs.aws.amazon.com/s3/index.html
[OVH Cloud]: https://www.ovhcloud.com/en-ie/public-cloud/object-storage/
[Seagate Lyve Cloud]: https://help.lyvecloud.seagate.com/en/s3-api-endpoints.html
[Google Cloud Storage]: https://cloud.google.com/distributed-cloud/hosted/docs/ga/gdch/apis/storage-s3-rest-api
[IONOS S3 Object Storage]: https://docs.ionos.com/cloud/managed-services/s3-object-storage
[DigitalOcean Spaces]: https://www.digitalocean.com/products/spaces
[Vultr Object Storage]: https://www.vultr.com/docs/vultr-object-storage
[Linode Object Storage]: https://www.linode.com/products/object-storage/
[S3 Compatible APIs]: https://github.com/sa7mon/S3Scanner/wiki/S3-Compatible-APIs
[Min.IO]: https://min.io/
[LakeFS]: https://lakefs.io/
[Ceph]: https://ceph.com/en/
[Riak]: https://riak.com/products/riak-s2/index.html
[NFS]: https://www.techtarget.com/searchenterprisedesktop/definition/Network-File-System
[iSCSI]: https://en.wikipedia.org/wiki/ISCSI
[WebDav]: https://en.wikipedia.org/wiki/WebDAV
[s3fs-fuse]: https://github.com/s3fs-fuse/s3fs-fuse
[Google Takeout]: https://takeout.google.com/settings/takeout
[PESOS]: https://indieweb.org/PESOS
[Samba]: https://www.samba.org/
