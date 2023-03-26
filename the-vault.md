---
title: The Vault
layout: default
nav_order: 10
---
# The Vault

> You favorite music, your personal photos, your notes, your favorite quotes and all your important files, including scanned documents. These are pieces of information that should be able to be stored indefinitely. For 50 or 100 years, especially if you'll still be alive by then.

It's nearly impossible to predict which storage solution will be around in 10 or 30 years, the safest bet it so store it using a popular structure that can be later migrated to modern systems if needed.

Requirements for the storage system:

1. To be replicated in multiple locations (no single point of failure, **resilient**).
1. To be encrypted at rest (**secure**).
1. To be encrypted in transport (**private**).
1. To be accessible via a simple, generic protocol (**generic**).

As the most popular storage system is the file system, for the purposes of The Vault, an object storage system might be a better solution, for two reasons:

1. it allows embedding of meta information for each object, this can be important information such as a description of an image that should be stored and processed together with the image.
1. it allows storing many small files without worrying about inodes[^1] and available space.

> **Object storage** is a computer data storage that manages data as objects, as opposed to other storage architectures like file systems which manages data as a file hierarchy, and block storage which manages data as blocks within sectors and tracks.[^2]

## S3 Compatible Storage

Several cloud services support object storage and offer a common interface known as the S3, from [Amazon's S3] service: [Linode Object Storage], [DigitalOcean Spaces], [OVH Cloud], [Seagate Lyve Cloud], [Google Cloud Storage] and even some that you might have not even heard about, like [IONOS S3 Object Storage] or [Vultr Object Storage]. See [S3 Compatible APIs] for a nice list of them.

They all offer object storage in the cloud, and provide an S3 compatible API interface to manage the data.

As many cloud services provide S3-compatible object storage, your data will be in their possession, not yours, so it's important to use a trustworthy service and/or, even more secure, to encrypt the data at rest. Most of them already have automated backups and replication, therefore they are considered to be reliable and resilient for as long as the owning company is in business. Occasionally there are breaches, security and maintenance should be a constant process.

For on-the-premises storage, there are a few options as well that include an S3-compatible API interface:

* [Ceph] provides a unified storage service with object, block, and file interfaces from a single cluster built from commodity hardware components.
* [Min.IO] offers high-performance, S3 compatible object storage. Native to Kubernetes, MinIO is the only object storage suite available on every public cloud, every Kubernetes distribution, the private cloud and the edge. MinIO is software-defined and is 100% open source under GNU AGPL v3.
* [Riak] provides a cost-effective solution that’s highly available, scalable and simple to use for storing all your images, text, video, documents, database backups and software binaries. Optimized for public, private or hybrid clouds, Riak S2 is Amazon S3- and OpenStack Swift-compatible, has robust APIs, and scales easily to handle petabytes of data using commodity software that provides near-linear performance increases as capacity is added.
* [LakeFS] is an open source data version control for data lakes. It enables zero copy Dev / Test isolated environments, continuous quality validation, atomic rollback on bad data, reproducibility, and more.

Depending on your needs and expertise, either of the above _should_ be fine.

## File System Storage

Object storage works great for data pulled in via APIs or other sync scripts, however for a document storage which is easily accessible from a personal computer, a mountable volume is preferred.

Ideally the storage solution should have a mountable interface (such as [NFS], [iSCSI] or [WebDav]).

A local storage system that has S3 can probably be mounted locally via various tools such as [s3fs-fuse] ([MinIO S3FS example](https://github.com/nitisht/cookbook/blob/master/docs/s3fs-fuse-with-minio.md)), however performance wise it's not a great idea.

## Online services sync

Services that have an API can be imported and synchronized automatically.

See [Anything to S3]({% link anything-to-s3.md %}) for a list of these.

## Manually importing data

Some services (such as Whatsapp) don't have an API, and thus must be imported manually. The import procedure might be automated to some extent, but exporting is usually done personally and manually.

----
[^1]: [wikipedia.org/wiki/Inode](https://en.wikipedia.org/wiki/Inode)
[^2]: [wikipedia.org/wiki/Object_storage](https://en.wikipedia.org/wiki/Object_storage)

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
