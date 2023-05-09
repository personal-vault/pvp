---
title: The Metadata
layout: default
nav_order: 15
has_children: false
parent: Concepts
---
# The Metadata

## What

> metadata [noun]: A set of data that describes and gives information about other data.

## Why

To enable the search and beyond (AI) to easily find relevant information for your query. Consider the following scenarios based on the file-type you have:

- `PDF`: you'll want a stripped down plain-text version of the PDF, perhaps including OCR on the images within the PDF, so you can index it
- `PPT(X)`: you'll want the plain-text data in the PPT
- `JPG`, `PNG`, `GIF`: you'll want EXIF info + description of the image (AI-powered)
- `MP3`: you'll want info such as album, artist, genre and even maybe BPM (some might be AI-powered in the future)
- `MP4`: you'll want the plaintext of whatever is in the video file, so you can index it. This can cost money.
- `AAC`, `MP3`: you'll want a transcription of what's in the audio file, so you can easily find conversations, songs, or voice notes
- `MD`, `TXT`: you'll want to know it's a plaintext file that you can fully index

Also, and this is pretty important, ability to add tags to files, therefore connecting them together. This way it's going to be easier to find connections between them.

One of the questions is if we should extract and store metadata, or extract it whenever we're indexing the data. For this project, and unlike Google and Yahoo, it might be costly for us to extract metadata (energy or money). Using AI/APIs to process audio or video files and extract/describe/transcribe content might cost.

This is why I am in favor of extracting the metadata and storing it somehow instead of generating it on the fly.

## How

Extracting the metadata should be a tool that processes any newly written/modified files and processes the files that haven't been processed from a metadata perspective.

There are tools that extract metadata from files:

- [Apache Tika]
- [ExifTool] by Phil Harvey
- File Information Tool Set ([FITS])

Storing the metadata can be done in various ways, such as:

1. used instantly (indexing),
2. saved in a separate file,
3. stored in a database, or
4.)stored as an extended attribute.

There are pros and cons for each option. It might be worth considering a configurable option.

### Used instantly

As previously stated, using the metadata instantly would (1) increase the indexing time quite a lot (2) increase costs (energy or money).

Pros:
- no need to store metadata separately;

Cons:
- involves costs each time we index the data;
- indexing will take much longer;

### Saved in a separate file

I've seen this in one of the exports, for every photo, there is a `.json` file that contains metadata for the given photo file.

Pros:
- metadata is in the same folder with the original file and can be moved around

Cons:
- duplicate the number of total files on the drive

### Database for metadata

Involves a table with metadata information that identifies a file by its path and/or hash. What we're aiming it to know if the metadata is still in sync with the file.

Pros:
- access to metadata is faster as database *should* be faster than disk;

Cons:
- metadata is completely separate from the original data, making it easy to go out of sync, one should periodically check that we still have the original files for the metadata, and that the metadata is about a file that exists;

### `xattr` for metadata

Regardless of where you move the file, as long as you don't modify its content, the metadata is valid and attached to the file. If it's separate, you either have to be careful to move the metadata as well, or it needs to be re-processed.

Pros:
- metadata stays with the file, even when moved or renamed;

Cons:
- requires that the file system supports xattrs and doesn't break them;
- size of attributes is limited based on OS (can even be limited to 64KB[^1]);


[^1]: https://man7.org/linux/man-pages/man7/xattr.7.html
[Apache Tika]: https://tika.apache.org
[ExifTool]: https://exiftool.org/
[FITS]: https://projects.iq.harvard.edu/fits
